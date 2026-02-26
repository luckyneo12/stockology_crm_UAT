<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #3b82f6; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { max-width: 150px; margin-bottom: 10px; }
        .title { font-size: 24px; font-weight: bold; color: #1e3a8a; margin: 0; }
        .section { margin-bottom: 25px; }
        .section-title { background: #f3f4f6; padding: 5px 10px; font-weight: bold; border-left: 4px solid #3b82f6; margin-bottom: 15px; text-transform: uppercase; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid td { padding: 8px 5px; vertical-align: top; border-bottom: 1px solid #efefef; }
        .label { font-weight: bold; width: 35%; color: #666; }
        .value { width: 65%; color: #111; }
        .photo-box { position: absolute; top: 100px; right: 40px; width: 100px; height: 120px; border: 1px solid #ccc; text-align: center; }
        .photo-img { width: 100px; height: 120px; object-fit: cover; }
        .signature-area { margin-top: 50px; text-align: right; }
        .signature-box { border-bottom: 1px solid #333; display: inline-block; width: 200px; height: 60px; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">E-KYC APPLICATION FORM</h1>
        <p>Digitally Signed Application for Account Opening</p>
    </div>

    @if($aadhaar_photo)
    <div class="photo-box">
        <img src="{{ $aadhaar_photo }}" class="photo-img">
    </div>
    @endif

    <div class="section">
        <div class="section-title">Personal Details</div>
        <table class="grid">
            <tr>
                <td class="label">Full Name</td>
                <td class="value">{{ $submission->pan_name }}</td>
            </tr>
            <tr>
                <td class="label">Email Address</td>
                <td class="value">{{ $submission->email }}</td>
            </tr>
            <tr>
                <td class="label">Mobile Number</td>
                <td class="value">{{ $submission->mobile_number }}</td>
            </tr>
            <tr>
                <td class="label">PAN Number</td>
                <td class="value">{{ $submission->pan_number }}</td>
            </tr>
            <tr>
                <td class="label">Aadhaar Number</td>
                <td class="value">XXXXXXXX{{ substr($submission->aadhaar_number, -4) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Bank Account Information</div>
        <table class="grid">
            <tr>
                <td class="label">Account Number</td>
                <td class="value">{{ $submission->bank_account_number }}</td>
            </tr>
            <tr>
                <td class="label">IFSC Code</td>
                <td class="value">{{ $submission->bank_ifsc }}</td>
            </tr>
            <tr>
                <td class="label">Bank Name</td>
                <td class="value">{{ $submission->bank_name ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    @if(!empty($submission->additional_data['geolocation']))
    <div class="section">
        <div class="section-title">Verification Details</div>
        <table class="grid">
            <tr>
                <td class="label">IP Address</td>
                <td class="value">{{ request()->ip() }}</td>
            </tr>
            <tr>
                <td class="label">Location (Lat/Long)</td>
                <td class="value">{{ $submission->additional_data['geolocation']['latitude'] }}, {{ $submission->additional_data['geolocation']['longitude'] }}</td>
            </tr>
            <tr>
                <td class="label">Face Verified At</td>
                <td class="value">{{ $submission->face_verified_at }}</td>
            </tr>
        </table>
    </div>
    @endif

    <div class="signature-area">
        <div style="margin-bottom: 5px;"><strong>Customer Digital Signature</strong></div>
        <div class="signature-box">
            @if(!empty($submission->additional_data['signature']))
                <img src="{{ $submission->additional_data['signature'] }}" style="max-height: 50px;">
            @endif
        </div>
        <p style="font-size: 9px; color: #666;">Digitally signed via Aadhaar e-Sign (Digio)</p>
    </div>

    <div class="footer">
        <p>This is a computer-generated document authenticated via Stockology E-KYC Platform on {{ date('d-m-Y H:i:s') }}.</p>
        <p>Stockology. All rights reserved.</p>
    </div>
</body>
</html>
