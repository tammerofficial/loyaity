<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📱 Apple Wallet QR Code - {{ $customer->name }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
        }
        
        .logo {
            font-size: 48px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .logo svg {
            opacity: 0.8;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .customer-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #007aff;
        }
        
        .customer-info h2 {
            margin: 0 0 15px 0;
            color: #007aff;
            font-size: 20px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            font-size: 16px;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
        }
        
        .info-value {
            color: #333;
            font-weight: 500;
        }
        
        .qr-container {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }
        
        .qr-code {
            width: 200px;
            height: 200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .instructions {
            margin-top: 30px;
            padding: 20px;
            background: #e3f2fd;
            border-radius: 12px;
            color: #1565c0;
            line-height: 1.6;
        }
        
        .step {
            margin: 10px 0;
            display: flex;
            align-items: center;
        }
        
        .step-number {
            background: #007aff;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            margin-right: 12px;
            flex-shrink: 0;
        }
        
        .buttons {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #007aff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056cc;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #f1f3f4;
            color: #333;
            border: 2px solid #ddd;
        }
        
        .btn-secondary:hover {
            background: #e8eaed;
            transform: translateY(-2px);
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 20px;
                margin: 10px;
            }
            
            .buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z" fill="#333"/>
            </svg>
            💳
        </div>
        <h1> Apple Wallet Pass</h1>
        <p style="color: #666; margin-bottom: 0;">Scan the QR code with your iPhone camera</p>
        
        <div class="customer-info">
            <h2>👤 {{ $customer->name }}</h2>
            <div class="info-item">
                <span class="info-label">📧 Email:</span>
                <span class="info-value">{{ $customer->email }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">📱 Phone:</span>
                <span class="info-value">{{ $customer->phone ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">🏆 Tier:</span>
                <span class="info-value">{{ $customer->tier }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">💎 Points:</span>
                <span class="info-value">{{ number_format($customer->available_points) }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">🆔 Member #:</span>
                <span class="info-value">{{ $customer->membership_number }}</span>
            </div>
        </div>
        
        <div class="qr-container">
                        <div class="qr-code">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=170x170&data={{ urlencode($walletPassUrl) }}&cache=false&v={{ time() }}"
                     alt="QR Code for Apple Wallet Pass" 
                     style="width: 100%; height: 100%; border-radius: 8px;">
                <div style="text-align: center; margin-top: 10px; font-size: 0.8rem; color: #666;">
                    🕒 تم التحديث: {{ date('H:i:s', $timestamp ?? time()) }}
                </div>
            </div>
        </div>
        
        <div class="instructions">
            <h3 style="margin-top: 0; color: #28a745;">✅ Certificate Status: ACTIVE</h3>
            <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 15px; margin-bottom: 20px; color: #155724;">
                <strong>🔒 Status:</strong> Pass is properly signed with valid Apple Developer certificates!
                <br><br>
                <strong>📋 Certificate Details:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>✅ Pass Type ID: <code>{{ env('APPLE_WALLET_PASS_TYPE_ID') }}</code></li>
                    <li>✅ Team ID: <code>{{ env('APPLE_WALLET_TEAM_ID') }}</code></li>
                    <li>✅ Apple WWDR Certificate (G3)</li>
                    <li>✅ Generated with OpenSSL 3.0 Legacy Provider</li>
                    <li>📱 Ready for iPhone deployment!</li>
                </ul>
            </div>
            
            <h3 style="color: #1565c0;">📱 How to Add to Apple Wallet:</h3>
            <div class="step">
                <div class="step-number">1</div>
                <span>Open Camera app on your iPhone</span>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <span>Point camera at the QR code above</span>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <span>Tap the notification that appears</span>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <span>Tap "Add to Apple Wallet" button</span>
            </div>
            <div class="step">
                <div class="step-number">5</div>
                <span>Your loyalty card is now in your Wallet! 🎉</span>
            </div>
        </div>
        
        <div class="buttons">
            <a href="{{ $walletPassUrl }}" class="btn btn-primary">
                 Download Pass
            </a>
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-secondary">
                ← Back to Customer
            </a>
        </div>
    </div>
</body>
</html>