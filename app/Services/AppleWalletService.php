<?php

namespace App\Services;

use App\Models\AppleWalletPass;
use App\Models\Customer;
use App\Models\WalletDesignSettings;
use App\Models\Logo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use ZipArchive;

class AppleWalletService
{
    private $teamId;
    private $passTypeId;
    private $certificatePath;
    private $certificatePassword;
    private $wwdrCertificatePath;

    public function __construct()
    {
        $this->teamId = env('APPLE_WALLET_TEAM_ID');
        $this->passTypeId = env('APPLE_WALLET_PASS_TYPE_ID');
        $this->certificatePath = storage_path('app/' . env('APPLE_WALLET_CERTIFICATE_PATH'));
        $this->certificatePassword = env('APPLE_WALLET_CERTIFICATE_PASSWORD');
        $this->wwdrCertificatePath = storage_path('app/' . env('APPLE_WALLET_WWDR_CERTIFICATE_PATH'));
    }

    public function generatePass(Customer $customer)
    {
        Log::info('Starting wallet pass generation', ['customer_id' => $customer->id, 'customer_name' => $customer->name]);
        
        // Create or update Apple Wallet pass record
        $pass = AppleWalletPass::firstOrCreate(
            ['customer_id' => $customer->id],
            [
                'pass_type_id' => $this->passTypeId,
                'serial_number' => '',
                'authentication_token' => '',
                'pass_data' => [],
                'is_active' => true,
            ]
        );

        if (empty($pass->serial_number)) {
            $pass->generateSerialNumber()->generateAuthenticationToken()->save();
        }

        Log::info('Pass record prepared', [
            'pass_id' => $pass->id,
            'serial_number' => $pass->serial_number,
            'authentication_token' => $pass->authentication_token
        ]);

        // Create pass directory
        $passDir = storage_path('app/passes/' . $pass->serial_number);
        if (!file_exists($passDir)) {
            mkdir($passDir, 0755, true);
            Log::info('Created pass directory', ['directory' => $passDir]);
        }

        // Get design settings for this customer
        $designSettings = WalletDesignSettings::getCustomerSettings($customer->id);
        $activeLogo = Logo::getActiveLogo();
        
        // Convert hex colors to RGB format
        $backgroundColor = $this->hexToRgb($designSettings->background_color);
        $textColor = $this->hexToRgb($designSettings->text_color);
        $labelColor = $this->hexToRgb($designSettings->label_color);
        
        // Generate pass.json
        $passJson = [
            'formatVersion' => 1,
            'passTypeIdentifier' => $this->passTypeId,
            'teamIdentifier' => $this->teamId,
            'serialNumber' => $pass->serial_number,
            'authenticationToken' => $pass->authentication_token,
            'webServiceURL' => config('loyalty.bridge_url'),
            'organizationName' => $designSettings->organization_name,
            'description' => 'Loyalty Card - ' . $designSettings->organization_name,
            'logoText' => $designSettings->organization_name,
            'foregroundColor' => "rgb({$textColor['r']}, {$textColor['g']}, {$textColor['b']})",
            'backgroundColor' => "rgb({$backgroundColor['r']}, {$backgroundColor['g']}, {$backgroundColor['b']})",
            'labelColor' => "rgb({$labelColor['r']}, {$labelColor['g']}, {$labelColor['b']})",
            'storeCard' => [
                'headerFields' => [
                    [
                        'key' => 'tier',
                        'label' => 'Tier',
                        'value' => strtoupper($customer->tier)
                    ]
                ],
                'primaryFields' => [
                    [
                        'key' => 'name',
                        'label' => '💎 Member',
                        'value' => $customer->name
                    ]
                ],
                'secondaryFields' => [
                    [
                        'key' => 'points',
                        'label' => 'Available Points',
                        'value' => number_format($customer->available_points)
                    ],
                    [
                        'key' => 'member_since',
                        'label' => 'Member Since',
                        'value' => $customer->joined_at ? $customer->joined_at->format('M Y') : 'N/A'
                    ]
                ],
                'auxiliaryFields' => [
                    [
                        'key' => 'membership_number',
                        'label' => 'Membership #',
                        'value' => $customer->membership_number
                    ]
                ],
                'backFields' => [
                    [
                        'key' => 'total_points',
                        'label' => 'Total Points Earned',
                        'value' => number_format($customer->total_points)
                    ],
                    [
                        'key' => 'terms',
                        'label' => 'Terms & Conditions',
                        'value' => 'Visit our website for complete terms and conditions. Points expire after 6 months.'
                    ]
                ]
            ],
            'barcode' => [
                'message' => json_encode([
                    'customer_id' => $customer->id,
                    'membership_number' => $customer->membership_number,
                    'points' => $customer->available_points,
                ]),
                'format' => 'PKBarcodeFormatQR',
                'messageEncoding' => 'iso-8859-1'
            ]
        ];

        file_put_contents($passDir . '/pass.json', json_encode($passJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Create placeholder images
        $this->createPassImages($passDir);

        // Create manifest
        $this->createManifest($passDir);

        // Create signature
        $this->createSignature($passDir);

        // Create ZIP file (pkpass)
        $pkpassPath = $this->createPkpassFile($passDir, $pass->serial_number);

        // Update pass record
        $pass->pass_data = $passJson;
        $pass->pkpass_url = url('storage/passes/' . $pass->serial_number . '.pkpass');
        $pass->last_updated = now();
        $pass->incrementDownloadCount();
        $pass->save();

        return $pkpassPath;
    }

    private function createPassImages($passDir)
    {
        Log::info('Creating pass images', ['pass_dir' => $passDir]);
        
        // Get the active logo from database
        $activeLogo = Logo::getActiveLogo();
        
        if ($activeLogo) {
            Log::info('Found active logo', [
                'logo_id' => $activeLogo->id,
                'logo_name' => $activeLogo->name,
                'external_url' => $activeLogo->external_url,
                'file_path' => $activeLogo->file_path
            ]);
            
            $logoContent = null;
            
            // Check if logo has external URL
            if ($activeLogo->external_url) {
                try {
                    Log::info('Attempting to download logo from external URL', ['url' => $activeLogo->external_url]);
                    
                    // Download logo from external URL
                    $response = Http::timeout(30)->get($activeLogo->external_url);
                    if ($response->successful()) {
                        $logoContent = $response->body();
                        Log::info('Successfully downloaded logo from external URL', [
                            'logo_id' => $activeLogo->id,
                            'logo_name' => $activeLogo->name,
                            'external_url' => $activeLogo->external_url,
                            'content_size' => strlen($logoContent)
                        ]);
                    } else {
                        Log::warning('Failed to download logo from external URL - HTTP error', [
                            'logo_id' => $activeLogo->id,
                            'external_url' => $activeLogo->external_url,
                            'status_code' => $response->status()
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to download logo from external URL - Exception', [
                        'logo_id' => $activeLogo->id,
                        'external_url' => $activeLogo->external_url,
                        'error' => $e->getMessage()
                    ]);
                }
            } elseif (Storage::exists($activeLogo->file_path)) {
                // Use the actual logo from storage
                $logoContent = Storage::get($activeLogo->file_path);
                Log::info('Using logo from local storage', [
                    'logo_id' => $activeLogo->id,
                    'logo_name' => $activeLogo->name,
                    'logo_path' => $activeLogo->file_path,
                    'content_size' => strlen($logoContent)
                ]);
            } else {
                Log::warning('Logo file not found in storage', [
                    'logo_id' => $activeLogo->id,
                    'file_path' => $activeLogo->file_path
                ]);
            }
            
            if ($logoContent) {
                // Standard logo sizes for Apple Wallet
                file_put_contents($passDir . '/logo.png', $logoContent);
                file_put_contents($passDir . '/logo@2x.png', $logoContent);
                file_put_contents($passDir . '/logo@3x.png', $logoContent);
                
                // Use the same logo for icon images
                file_put_contents($passDir . '/icon.png', $logoContent);
                file_put_contents($passDir . '/icon@2x.png', $logoContent);
                file_put_contents($passDir . '/icon@3x.png', $logoContent);
                
                Log::info('Logo files successfully created in pass directory', [
                    'logo_id' => $activeLogo->id,
                    'logo_name' => $activeLogo->name,
                    'files_created' => [
                        'logo.png',
                        'logo@2x.png', 
                        'logo@3x.png',
                        'icon.png',
                        'icon@2x.png',
                        'icon@3x.png'
                    ]
                ]);
                return;
            }
        } else {
            Log::warning('No active logo found in database');
        }
        
        // Fallback to placeholder if no active logo or download failed
        $logoContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        
        // Standard logo sizes for Apple Wallet
        file_put_contents($passDir . '/logo.png', $logoContent);
        file_put_contents($passDir . '/logo@2x.png', $logoContent);
        file_put_contents($passDir . '/logo@3x.png', $logoContent);
        
        // Optional: Add icon images
        file_put_contents($passDir . '/icon.png', $logoContent);
        file_put_contents($passDir . '/icon@2x.png', $logoContent);
        file_put_contents($passDir . '/icon@3x.png', $logoContent);
        
        Log::warning('Using placeholder logo for wallet pass');
    }

    private function createManifest($passDir)
    {
        $manifest = [];
        $files = glob($passDir . '/*');
        
        foreach ($files as $file) {
            if (is_file($file) && basename($file) !== 'manifest.json' && basename($file) !== 'signature') {
                $manifest[basename($file)] = sha1_file($file);
            }
        }

        file_put_contents($passDir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
        return $manifest;
    }

    private function createSignature($passDir)
    {
        $manifestJson = $passDir . '/manifest.json';
        $signaturePath = $passDir . '/signature';

        // Try to create a real signature if certificate exists
        if (file_exists($this->certificatePath)) {
            try {
                $certData = file_get_contents($this->certificatePath);
                $pkcs12 = [];
                
                if (openssl_pkcs12_read($certData, $pkcs12, $this->certificatePassword)) {
                    openssl_pkcs7_sign(
                        $manifestJson,
                        $signaturePath,
                        $pkcs12['cert'],
                        $pkcs12['pkey'],
                        [],
                        PKCS7_BINARY | PKCS7_DETACHED
                    );
                    
                    // Process signature file
                    $signatureData = file_get_contents($signaturePath);
                    $signatureData = str_replace("-----BEGIN PKCS7-----", "", $signatureData);
                    $signatureData = str_replace("-----END PKCS7-----", "", $signatureData);
                    $signatureData = str_replace("\n", "", $signatureData);
                    $signatureData = base64_decode($signatureData);
                    
                    file_put_contents($signaturePath, $signatureData);
                    return;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to create real signature: ' . $e->getMessage());
            }
        }

        // Create dummy signature if real one fails
        file_put_contents($signaturePath, 'dummy_signature_for_testing');
        Log::info('Created dummy signature - real certificate not available');
    }

    private function createPkpassFile($passDir, $serialNumber)
    {
        $pkpassPath = storage_path('app/public/passes/' . $serialNumber . '.pkpass');
        
        // Ensure public passes directory exists
        $publicPassesDir = storage_path('app/public/passes');
        if (!file_exists($publicPassesDir)) {
            mkdir($publicPassesDir, 0755, true);
        }

        $zip = new ZipArchive();
        
        if ($zip->open($pkpassPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $files = glob($passDir . '/*');
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    $zip->addFile($file, basename($file));
                }
            }
            
            $zip->close();
            
            // Clean up temporary directory
            $this->deleteDirectory($passDir);
            
            return $pkpassPath;
        }

        throw new \Exception('Failed to create pkpass file');
    }

    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    public function updatePass(AppleWalletPass $pass)
    {
        $customer = $pass->customer;
        return $this->generatePass($customer);
    }

    public function signPass($passData)
    {
        // This method can be used for additional signing operations
        return $passData;
    }

    /**
     * Convert hex color to RGB array
     */
    private function hexToRgb($hex)
    {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        ];
    }
}
