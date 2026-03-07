<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Membership Card | Citizens Movement for Change</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            font-family: Arial, sans-serif;
            background-color: #fff;
        }

        .page-wrapper {
            width: 210mm;
            height: 297mm;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-left: 30%;
            padding-top: 35%;
            page-break-after: avoid;
            page-break-before: avoid;
        }

        .card {
            width: 306px;
            height: 486px;
            position: relative;
            background: #fff;
            overflow: hidden;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-sizing: border-box;
            padding: 15px;
        }

        .corner-top-left {
            position: absolute;
            top: 0;
            left: 0;
            border-style: solid;
            border-width: 100px 100px 0 0;
            border-color: #f00 transparent transparent transparent;
        }

        .corner-bottom-right {
            position: absolute;
            bottom: 0;
            right: 0;
            border-style: solid;
            border-width: 0 0 100px 100px;
            border-color: transparent transparent #0047ab transparent;
        }

        .card-header {
            text-align: center;
            margin-top: 15px;
        }

        .card-header img {
            width: 70px;
            height: 70px;
        }

        .organization-name {
            color: #0047ab;
            font-weight: bold;
            font-size: 14px;
            margin-top: 5px;
        }

        .tagline {
            font-size: 10px;
            font-style: italic;
            color: #555;
            margin-top: 2px;
        }

        .profile-image-wrapper {
            width: 100px;
            height: 100px;
            border-radius: 5px;
            overflow: hidden;
            border: 1px solid #ddd;
            margin: 15px auto 10px auto;
        }

        .profile-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .member-name {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #0047ab;
            margin-top: 5px;
        }

        .member-role {
            text-align: center;
            font-size: 13px;
            color: #555;
            margin-bottom: 15px;
        }

        #information {
            width: 80%;
            margin: 0 auto;
        }

        .info-table {
            width: 80%;
            border-collapse: collapse;
            border: none;
            margin-top: 10px;
            margin-left: 35px;
        }

        .info-left {
            width: 50%;
            padding-left: 10px;
            vertical-align: middle;
        }

        .info-label {
            font-weight: bold;
            color: #0047ab;
            margin-bottom: 2px;
            font-size: 11px;
        }

        .info-value {
            margin-bottom: 6px;
            font-size: 11px;
        }

        .qr-code-cell {
            width: 50%;
            text-align: center;
            vertical-align: middle;
        }

        .qr-code-cell img {
            width: 100px;
            height: 100px;
            object-fit: contain;
        }
    </style>
</head>

<body>
    <div class="page-wrapper">
        <div class="card">
            <div class="corner-top-left"></div>
            <div class="corner-bottom-right"></div>

            <div class="card-header">
                <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo" />
                <div class="organization-name">CITIZENS MOVEMENT FOR CHANGE</div>
                <div class="tagline">For the people, By the people</div>
            </div>

            <div class="profile-image-wrapper">
                <img src="data:image/jpeg;base64,{{ $avatarBase64 }}" alt="{{ $user->name }}" />
            </div>

            <div class="member-name">{{ $user->name }}</div>
            <div class="member-role">Member</div>

            <div id="information">
                <table class="info-table">
                    <tr>
                        <td class="qr-code-cell">
                            <img src="data:image/png;base64,{{ $qrCode }}" alt="QR code to verify membership" />
                        </td>
                        <td class="info-left">

                            <div class="info-label">Gender</div>
                            <div class="info-value">{{ ucfirst($user->profile->gender) }}</div>

                            <div class="info-label">D.O.B</div>
                            <div class="info-value">{{ \Carbon\Carbon::parse($user->dob)->format('d/m/Y') }}</div>

                            <div class="info-label">Date of issue</div>
                            <div class="info-value">{{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y') }}</div>

                        </td>
                    </tr>
                </table>
            </div>
            <i style="font-size: 10px; opacity: 0.5; position: absolute; bottom: 10px;"><small>ID No. {{ $user->slug }}</small></i>
        </div>
    </div>
</body>

</html>
