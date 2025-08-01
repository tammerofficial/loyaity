<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>🎨 Apple Wallet Pass Design - {{ $customer->name }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 600;
        }

        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .content {
            padding: 40px;
        }

        .preview-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: start;
        }

        .card-preview {
            position: relative;
            perspective: 1000px;
        }

        .wallet-card {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            border-radius: 20px;
            padding: 30px;
            color: white;
            box-shadow: 0 20px 50px rgba(30, 58, 138, 0.4);
            transform: rotateY(-3deg) rotateX(3deg);
            transition: transform 0.3s ease;
            min-height: 450px;
            position: relative;
            overflow: hidden;
            width: 100%;
            max-width: 400px;
        }

        .wallet-card:hover {
            transform: rotateY(0deg) rotateX(0deg) scale(1.02);
        }

        .wallet-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .card-header {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 20px;
            gap: 12px;
        }

        .card-logo {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .apple-logo, .default-logo {
            width: 24px;
            height: 24px;
            fill: white;
        }

        .custom-logo {
            max-height: 24px;
            max-width: 60px;
            object-fit: contain;
            filter: brightness(0) invert(1); /* جعل الشعار أبيض */
        }

        .hidden {
            display: none !important;
        }

        .card-body {
            margin-bottom: 25px;
        }

        .customer-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 8px;
            opacity: 0.9;
            letter-spacing: 0.5px;
        }

        .points-display {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .points-label {
            font-size: 0.9rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .card-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 20px;
        }

        .detail-item {
            text-align: center;
        }

        .detail-value {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .detail-label {
            font-size: 0.8rem;
            opacity: 0.7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .barcode-area {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.2);
            text-align: center;
        }

        .barcode-placeholder {
            background: rgba(255,255,255,0.9);
            color: #1e3a8a;
            padding: 8px 12px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 0.9rem;
            display: inline-block;
            margin-top: 5px;
        }

        /* Customer Info Section */
        .customer-info-section {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.15);
        }

        .customer-name-main {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .member-number {
            font-size: 0.9rem;
            opacity: 0.85;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            background: rgba(255,255,255,0.1);
            padding: 4px 12px;
            border-radius: 12px;
            display: inline-block;
        }

        /* Points Section */
        .points-section {
            margin-bottom: 25px;
        }

        .points-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255,255,255,0.08);
            border-radius: 15px;
            padding: 20px;
        }

        .points-available {
            text-align: left;
        }

        .points-value {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 2px;
            text-shadow: 0 2px 6px rgba(0,0,0,0.3);
        }

        .points-label {
            font-size: 0.8rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .points-total {
            text-align: right;
        }

        .points-value-small {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 2px;
            opacity: 0.9;
        }

        .points-label-small {
            font-size: 0.7rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 500;
        }

        /* Details Grid */
        .details-grid {
            margin-bottom: 25px;
        }

        .detail-row {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
        }

        .detail-row.single {
            margin-bottom: 20px;
        }

        .details-grid .detail-item {
            display: flex;
            align-items: center;
            background: rgba(255,255,255,0.06);
            border-radius: 12px;
            padding: 12px;
            flex: 1;
            min-width: 0;
            text-align: left;
        }

        .detail-item.full-width {
            width: 100%;
        }

        .detail-icon {
            font-size: 1.1rem;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .detail-content {
            flex: 1;
            min-width: 0;
        }

        .details-grid .detail-value {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 2px;
            color: #ffffff;
        }

        .detail-value-small {
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 2px;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .details-grid .detail-label {
            font-size: 0.7rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        /* QR Code Section */
        .qr-section {
            border-top: 1px solid rgba(255,255,255,0.15);
            padding-top: 20px;
            text-align: center;
        }

        .qr-code-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .qr-code-placeholder {
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed rgba(255,255,255,0.3);
        }

        .qr-pattern {
            width: 30px;
            height: 30px;
            background: 
                linear-gradient(90deg, rgba(255,255,255,0.8) 50%, transparent 50%),
                linear-gradient(rgba(255,255,255,0.8) 50%, transparent 50%);
            background-size: 4px 4px;
            border-radius: 2px;
        }

        .qr-label {
            font-size: 0.8rem;
            opacity: 0.9;
            font-weight: 600;
            letter-spacing: 0.5px;
            flex: 1;
            text-align: right;
            padding-right: 10px;
        }

        .customization-panel {
            background: #f8fafc;
            border-radius: 15px;
            padding: 25px;
            border: 2px solid #e2e8f0;
        }

        .customization-panel h3 {
            margin: 0 0 20px 0;
            color: #1e293b;
            font-size: 1.3rem;
        }

        .custom-group {
            margin-bottom: 20px;
        }

        .custom-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }

        .custom-input {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }

        .custom-input:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .color-picker {
            width: 50px;
            height: 40px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .color-group {
            display: grid;
            grid-template-columns: 1fr 60px;
            gap: 10px;
            align-items: center;
        }

        .buttons {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .feature-badge {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 10px;
        }

        .current-values {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .current-values h4 {
            margin: 0 0 10px 0;
            color: #1e40af;
            font-size: 1rem;
        }

        .value-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .value-label {
            color: #6b7280;
        }

        .value-data {
            font-weight: 600;
            color: #1f2937;
        }

        @media (max-width: 768px) {
            .preview-section {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎨 Apple Wallet Pass Design</h1>
            <p>Customer: <strong>{{ $customer->name }}</strong> <span class="feature-badge">Live Preview</span></p>
        </div>

        <div class="content">
            <div class="preview-section">
                <!-- Card Preview -->
                <div class="card-preview">
                    <div class="wallet-card" id="previewCard">
                        <!-- Header Section -->
                        <div class="card-header">
                            <div class="logo-container" id="logoContainer">
                                <svg class="apple-logo default-logo" viewBox="0 0 24 24" id="defaultLogo">
                                    <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                                </svg>
                                <img id="customLogo" class="custom-logo hidden" alt="Custom Logo" style="max-height: 24px; max-width: 60px;">
                            </div>
                            <div class="card-logo" id="organizationName">Tammer Loyalty</div>
                        </div>

                        <!-- Customer Info Section -->
                        <div class="customer-info-section">
                            <div class="customer-name-main" id="customerNameMain">💎 {{ $customer->name }}</div>
                            <div class="member-number" id="memberNumberDisplay">{{ $customer->membership_number }}</div>
                        </div>

                        <!-- Points Section -->
                        <div class="points-section">
                            <div class="points-container">
                                <div class="points-available">
                                    <div class="points-value" id="pointsAvailable">{{ number_format($customer->available_points) }}</div>
                                    <div class="points-label">Available Points</div>
                                </div>
                                <div class="points-total">
                                    <div class="points-value-small" id="pointsTotal">{{ number_format($customer->total_points) }}</div>
                                    <div class="points-label-small">Total Earned</div>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Details Grid -->
                        <div class="details-grid">
                            <div class="detail-row">
                                <div class="detail-item">
                                    <div class="detail-icon">🏆</div>
                                    <div class="detail-content">
                                        <div class="detail-value" id="tierDisplay">{{ $customer->tier }}</div>
                                        <div class="detail-label">Tier Level</div>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-icon">📧</div>
                                    <div class="detail-content">
                                        <div class="detail-value-small" id="emailDisplay">{{ $customer->email }}</div>
                                        <div class="detail-label">Email</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-item">
                                    <div class="detail-icon">📱</div>
                                    <div class="detail-content">
                                        <div class="detail-value-small" id="phoneDisplay">{{ $customer->phone }}</div>
                                        <div class="detail-label">Phone</div>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-icon">🎂</div>
                                    <div class="detail-content">
                                        <div class="detail-value-small" id="dobDisplay">{{ date('M d, Y', strtotime($customer->date_of_birth)) }}</div>
                                        <div class="detail-label">Birthday</div>
                                    </div>
                                </div>
                            </div>

                            <div class="detail-row single">
                                <div class="detail-item full-width">
                                    <div class="detail-icon">📅</div>
                                    <div class="detail-content">
                                        <div class="detail-value-small" id="joinedDisplay">Member since {{ date('M d, Y', strtotime($customer->joined_at)) }}</div>
                                        <div class="detail-label">Membership</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- QR Code Section -->
                        <div class="qr-section">
                            <div class="qr-code-container">
                                <div class="qr-code-placeholder">
                                    <div class="qr-pattern"></div>
                                </div>
                                <div class="qr-label">Scan to Add to Wallet</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customization Panel -->
                <div class="customization-panel">
                    <h3>🎨 تخصيص التصميم</h3>

                    <!-- Current Values -->
                    <div class="current-values">
                        <h4>📊 القيم الحالية</h4>
                        <div class="value-item">
                            <span class="value-label">العميل:</span>
                            <span class="value-data">{{ $customer->name }}</span>
                        </div>
                        <div class="value-item">
                            <span class="value-label">النقاط المتاحة:</span>
                            <span class="value-data">{{ number_format($customer->available_points) }}</span>
                        </div>
                        <div class="value-item">
                            <span class="value-label">المستوى:</span>
                            <span class="value-data">{{ $customer->tier }}</span>
                        </div>
                        <div class="value-item">
                            <span class="value-label">رقم العضوية:</span>
                            <span class="value-data">{{ $customer->membership_number }}</span>
                        </div>
                    </div>

                    <!-- Save Options -->
                    <div class="custom-group">
                        <label>💾 نطاق التأثير</label>
                        <select class="custom-input" id="applyTo">
                            <option value="customer">هذا العميل فقط ({{ $customer->name }})</option>
                            <option value="global">جميع العملاء (إعدادات عامة)</option>
                        </select>
                    </div>

                    <!-- Customization Options -->
                    <div class="custom-group">
                        <label>🏢 اسم الشركة</label>
                        <input type="text" class="custom-input" id="orgName" value="{{ $designSettings->organization_name }}" 
                               onchange="updatePreview()">
                    </div>

                    <div class="custom-group">
                        <label>🎨 لون الخلفية الرئيسي</label>
                        <div class="color-group">
                            <input type="text" class="custom-input" id="bgColorText" value="{{ $designSettings->background_color }}" 
                                   onchange="updatePreview()">
                            <input type="color" class="color-picker" id="bgColor" value="{{ $designSettings->background_color }}" 
                                   onchange="syncColor('bg')">
                        </div>
                    </div>

                    <div class="custom-group">
                        <label>🌟 لون الخلفية الثانوي</label>
                        <div class="color-group">
                            <input type="text" class="custom-input" id="bg2ColorText" value="{{ $designSettings->background_color_secondary }}" 
                                   onchange="updatePreview()">
                            <input type="color" class="color-picker" id="bg2Color" value="{{ $designSettings->background_color_secondary }}" 
                                   onchange="syncColor('bg2')">
                        </div>
                    </div>

                    <div class="custom-group">
                        <label>📝 لون النص</label>
                        <div class="color-group">
                            <input type="text" class="custom-input" id="textColorText" value="{{ $designSettings->text_color }}" 
                                   onchange="updatePreview()">
                            <input type="color" class="color-picker" id="textColor" value="{{ $designSettings->text_color }}" 
                                   onchange="syncColor('text')">
                        </div>
                    </div>

                    <div class="custom-group">
                        <label>🏷️ لون التسميات</label>
                        <div class="color-group">
                            <input type="text" class="custom-input" id="labelColorText" value="{{ $designSettings->label_color }}" 
                                   onchange="updatePreview()">
                            <input type="color" class="color-picker" id="labelColor" value="{{ $designSettings->label_color }}" 
                                   onchange="syncColor('label')">
                        </div>
                    </div>

                    <div class="custom-group">
                        <label>
                            <input type="checkbox" id="useBackgroundImage" {{ $designSettings->use_background_image ? 'checked' : '' }} 
                                   onchange="toggleBackgroundImage()" style="margin-right: 8px;">
                            🖼️ استخدام خلفية مخصصة
                        </label>
                    </div>

                    <div id="backgroundImageOptions" style="display: {{ $designSettings->use_background_image ? 'block' : 'none' }};">
                        <div class="custom-group">
                            <label>🌐 رابط الصورة</label>
                            <input type="url" class="custom-input" id="backgroundImageUrl" 
                                   value="{{ $designSettings->background_image_url ?? '' }}" 
                                   placeholder="https://example.com/image.jpg"
                                   onchange="updatePreview()">
                        </div>

                        <div class="custom-group">
                            <label>🔍 شفافية الخلفية: <span id="opacityLabel">{{ $designSettings->background_opacity ?? 50 }}%</span></label>
                            <input type="range" class="custom-input" id="backgroundOpacity" 
                                   min="0" max="100" value="{{ $designSettings->background_opacity ?? 50 }}" 
                                   oninput="updateOpacityLabel(); updatePreview();"
                                   style="width: 100%;">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="buttons">
                        <button onclick="saveDesign()" class="btn btn-success" id="saveBtn">
                            💾 حفظ التصميم
                        </button>
                        <a href="{{ route('admin.logos.index') }}" class="btn btn-primary" target="_blank">
                            🎨 إدارة الشعارات
                        </a>
                        <a href="{{ route('admin.customers.wallet-pass', $customer) }}" class="btn btn-primary">
                            📱 تحميل البطاقة المحدثة
                        </a>
                        <a href="{{ route('admin.customers.wallet-qr', $customer) }}" class="btn btn-secondary">
                            🔗 عرض QR Code
                        </a>
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">
                            ← العودة للعملاء
                        </a>
                    </div>

                    <!-- Status Messages -->
                    <div id="statusMessage" style="margin-top: 15px; padding: 12px; border-radius: 8px; display: none;">
                    </div>

                    <!-- Design Info -->
                    <div style="margin-top: 25px; padding: 15px; background: #d1fae5; border-radius: 10px; border: 1px solid #10b981;">
                        <h4 style="margin: 0 0 10px 0; color: #065f46;">✅ كيفية عمل النظام</h4>
                        <ul style="margin: 0; padding-left: 20px; color: #065f46; font-size: 0.9rem;">
                            <li><strong>معاينة مباشرة:</strong> التغييرات تظهر فوراً في البطاقة أعلاه</li>
                            <li><strong>حفظ للعميل:</strong> اختر "هذا العميل فقط" لحفظ تصميم مخصص له</li>
                            <li><strong>حفظ للكل:</strong> اختر "جميع العملاء" لتطبيق التصميم على جميع البطاقات الجديدة</li>
                            <li><strong>تأثير فوري:</strong> بعد الحفظ، البطاقات الجديدة ستستخدم التصميم المحدث</li>
                            <li><strong>رفع تلقائي:</strong> البطاقات تُرفع للسيرفر `192.168.8.143` تلقائياً</li>
                        </ul>
                    </div>

                    <div style="margin-top: 15px; padding: 15px; background: #eff6ff; border-radius: 10px; border: 1px solid #3b82f6;">
                        <h4 style="margin: 0 0 10px 0; color: #1e40af;">🎨 ميزات التصميم</h4>
                        <ul style="margin: 0; padding-left: 20px; color: #1e40af; font-size: 0.9rem;">
                            <li>تحويل تلقائي من hex إلى RGB للتوافق مع Apple Wallet</li>
                            <li>حفظ في قاعدة البيانات لضمان الاستمرارية</li>
                            <li>إمكانية التخصيص لكل عميل أو للكل</li>
                            <li>تطبيق فوري على البطاقات الجديدة</li>
                        </ul>
                    </div>

                    <div style="margin-top: 15px; padding: 15px; background: #f0f9ff; border-radius: 10px; border: 1px solid #0ea5e9;">
                        <h4 style="margin: 0 0 10px 0; color: #0c4a6e;">🔔 تحديث البطاقات الموجودة</h4>
                        <ul style="margin: 0; padding-left: 20px; color: #0c4a6e; font-size: 0.9rem;">
                            <li><strong>🚀 ميزة جديدة:</strong> البطاقات الموجودة في Apple Wallet ستتحدث تلقائياً!</li>
                            <li><strong>📱 Push Notifications:</strong> إرسال إشعارات للأجهزة المسجلة</li>
                            <li><strong>🔄 تحديث فوري:</strong> البطاقات ستُحدث في الخلفية بدون تدخل المستخدم</li>
                            <li><strong>📊 تتبع:</strong> عرض عدد الإشعارات المرسلة بعد الحفظ</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function syncColor(type) {
            const colorPicker = document.getElementById(type + 'Color');
            const textInput = document.getElementById(type + 'ColorText');
            textInput.value = colorPicker.value;
            updatePreview();
        }

        function updatePreview() {
            const card = document.getElementById('previewCard');
            const orgName = document.getElementById('orgName').value;
            const bgColor = document.getElementById('bgColorText').value;
            const bg2Color = document.getElementById('bg2ColorText').value;
            const textColor = document.getElementById('textColorText').value;
            const labelColor = document.getElementById('labelColorText').value;
            
            // Update organization name
            document.getElementById('organizationName').textContent = orgName;
            
            // Handle background image
            const useBackgroundImage = document.getElementById('useBackgroundImage').checked;
            const backgroundImageUrl = document.getElementById('backgroundImageUrl').value;
            const backgroundOpacity = document.getElementById('backgroundOpacity').value;
            
            if (useBackgroundImage && backgroundImageUrl) {
                // Create gradient with background image
                const opacityValue = backgroundOpacity / 100;
                card.style.background = `
                    linear-gradient(135deg, 
                        rgba(${hexToRgbValues(bgColor).join(',')}, ${opacityValue}) 0%, 
                        rgba(${hexToRgbValues(bg2Color).join(',')}, ${opacityValue}) 100%
                    ),
                    url('${backgroundImageUrl}')
                `;
                card.style.backgroundSize = 'cover';
                card.style.backgroundPosition = 'center';
                card.style.backgroundBlendMode = 'overlay';
            } else {
                // Use solid gradient
                card.style.background = `linear-gradient(135deg, ${bgColor} 0%, ${bg2Color} 100%)`;
                card.style.backgroundSize = 'auto';
                card.style.backgroundPosition = 'initial';
                card.style.backgroundBlendMode = 'normal';
            }
            
            card.style.color = textColor;
            
            // Update label colors for all label elements
            const labels = card.querySelectorAll('.detail-label, .points-label, .points-label-small, .qr-label');
            labels.forEach(label => {
                label.style.color = labelColor;
            });
            
            // Update all detail labels in the new grid
            const detailLabels = card.querySelectorAll('.details-grid .detail-label');
            detailLabels.forEach(label => {
                label.style.color = labelColor;
            });
            
            // Update color pickers to match text inputs
            document.getElementById('bgColor').value = bgColor;
            document.getElementById('bg2Color').value = bg2Color;
            document.getElementById('textColor').value = textColor;
            document.getElementById('labelColor').value = labelColor;
        }

        function hexToRgbValues(hex) {
            // Remove # if present
            hex = hex.replace('#', '');
            
            // Convert hex to RGB values
            const r = parseInt(hex.substring(0, 2), 16);
            const g = parseInt(hex.substring(2, 4), 16);
            const b = parseInt(hex.substring(4, 6), 16);
            
            return [r, g, b];
        }

        function toggleBackgroundImage() {
            const options = document.getElementById('backgroundImageOptions');
            const checkbox = document.getElementById('useBackgroundImage');
            
            if (checkbox.checked) {
                options.style.display = 'block';
            } else {
                options.style.display = 'none';
            }
            
            updatePreview();
        }

        function updateOpacityLabel() {
            const opacity = document.getElementById('backgroundOpacity').value;
            document.getElementById('opacityLabel').textContent = opacity + '%';
        }

        async function saveDesign() {
            const saveBtn = document.getElementById('saveBtn');
            const statusMessage = document.getElementById('statusMessage');
            
            // Disable button and show loading
            saveBtn.disabled = true;
            saveBtn.textContent = '⏳ جاري الحفظ...';
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const formData = {
                organization_name: document.getElementById('orgName').value,
                background_color: document.getElementById('bgColorText').value,
                background_color_secondary: document.getElementById('bg2ColorText').value,
                text_color: document.getElementById('textColorText').value,
                label_color: document.getElementById('labelColorText').value,
                background_image_url: document.getElementById('backgroundImageUrl').value,
                background_opacity: document.getElementById('backgroundOpacity').value,
                use_background_image: document.getElementById('useBackgroundImage').checked,
                apply_to: document.getElementById('applyTo').value,
                _token: csrfToken
            };

            try {
                const response = await fetch('{{ route("admin.customers.wallet-design", $customer) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();
                
                if (result.success) {
                    statusMessage.style.display = 'block';
                    statusMessage.style.background = '#d4edda';
                    statusMessage.style.border = '1px solid #c3e6cb';
                    statusMessage.style.color = '#155724';
                    
                    let message = `✅ ${result.message}`;
                    if (result.notifications_sent !== undefined) {
                        if (result.notifications_sent > 0) {
                            message += `<br/>📱 تم إرسال ${result.notifications_sent} إشعار push للأجهزة المسجلة.`;
                        } else {
                            message += `<br/>📱 لا توجد أجهزة مسجلة حالياً. ستطبق التغييرات على البطاقات الجديدة.`;
                        }
                    }
                    
                    statusMessage.innerHTML = message;
                    
                    // Update download link to reflect changes
                    setTimeout(() => {
                        const downloadLink = document.querySelector('a[href*="wallet-pass"]');
                        if (downloadLink) {
                            downloadLink.style.animation = 'pulse 2s infinite';
                            downloadLink.innerHTML = '🔄 تحميل البطاقة المحدثة';
                        }
                    }, 1000);
                } else {
                    throw new Error(result.message || 'حدث خطأ أثناء الحفظ');
                }
            } catch (error) {
                statusMessage.style.display = 'block';
                statusMessage.style.background = '#f8d7da';
                statusMessage.style.border = '1px solid #f5c6cb';
                statusMessage.style.color = '#721c24';
                statusMessage.innerHTML = `❌ خطأ: ${error.message}`;
            } finally {
                // Re-enable button
                saveBtn.disabled = false;
                saveBtn.textContent = '💾 حفظ التصميم';
                
                // Hide message after 5 seconds
                setTimeout(() => {
                    statusMessage.style.display = 'none';
                }, 5000);
            }
        }

        // Update logo display
        function updateLogo() {
            fetch('{{ route("admin.logos.active-api") }}')
                .then(response => response.json())
                .then(data => {
                    const defaultLogo = document.getElementById('defaultLogo');
                    const customLogo = document.getElementById('customLogo');
                    
                    if (data.success && data.logo) {
                        // إظهار الشعار المخصص
                        customLogo.src = data.logo.url;
                        customLogo.alt = data.logo.name;
                        customLogo.classList.remove('hidden');
                        defaultLogo.classList.add('hidden');
                    } else {
                        // إظهار الشعار الافتراضي
                        customLogo.classList.add('hidden');
                        defaultLogo.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.log('استخدام الشعار الافتراضي');
                    document.getElementById('customLogo').classList.add('hidden');
                    document.getElementById('defaultLogo').classList.remove('hidden');
                });
        }

        // Initialize color sync and logo
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners to text inputs
            ['bgColorText', 'bg2ColorText', 'textColorText', 'labelColorText'].forEach(id => {
                document.getElementById(id).addEventListener('input', updatePreview);
            });

            // Initialize preview with current settings
            updatePreview();
            
            // تحديث الشعار عند تحميل الصفحة
            updateLogo();
            
            // تحديث الشعار كل 3 ثواني للمراقبة المباشرة
            setInterval(updateLogo, 3000);
            
            // تحديث الشعار عند العودة للصفحة (focus)
            window.addEventListener('focus', updateLogo);
        });
    </script>
</body>
</html>