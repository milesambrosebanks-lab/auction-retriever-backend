<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Membership Cards | Citizens Movement for Change</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f3f3;
            margin: 0;
            padding: 20px;
        }

        @page {
            size: 3.483in 5.69in;
            /* 0.1in larger than card on both dimensions */
            margin: 0 !important;
            padding: 0 !important;
        }

        @media print {

            body {
                background-color: white;
                padding: 0;
                margin: 0;
            }

            .card {
                page-break-after: always;
                margin: 0;
                box-shadow: none;
                border-radius: 0;
                width: 3.383in;
                height: 5.39in;
                /* Center the card on the slightly larger page */
                position: relative;
                left: 0.05in;
                /* (3.483 - 3.383)/2 */
                top: 0.05in;
                /* (5.49 - 5.39)/2 */
            }

            .print-button,
            .generate-button {
                display: none;
            }

            .card-container {
                display: block;
            }

        }

        .controls {
            text-align: center;
            margin-bottom: 20px;
        }

        .print-button,
        .generate-button {
            padding: 10px 20px;
            background-color: #0047ab;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 0 10px;
            font-size: 16px;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .card {
            width: 3.383in;
            height: 5.39in;
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 12px;
            box-sizing: border-box;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 0;
            border-top: 50px solid red;
            border-right: 50px solid transparent;
            z-index: 1;
        }

        .card::after {
            content: "";
            position: absolute;
            bottom: 0;
            right: 0;
            width: 0;
            height: 0;
            border-bottom: 50px solid #0047ab;
            border-left: 50px solid transparent;
            z-index: 1;
        }

        .card .corner-circle {
            position: absolute;
            width: 73px;
            height: 35px;
            background-color: white;
            border-radius: 50%;
            z-index: 2;
            transform: rotate(135deg);
        }

        .card .top-left-circle {
            top: 10px;
            left: -10px;
        }

        .card .bottom-right-circle {
            bottom: 10px;
            right: -10px;
        }

        .header {
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
            margin-bottom: 12px;
        }

        .header-logo {
            width: 60px;
            height: 60px;
        }

        .header-title {
            font-size: 16px;
            font-weight: bold;
            color: #0047ab;
            line-height: 1.1;
        }

        .header-tagline {
            font-size: 10px;
            font-style: italic;
            color: #333;
            margin-top: 3px;
        }

        .profile {
            text-align: center;
            margin-top: 10px;
        }

        .profile img {
            width: 160px;
            height: 160px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .name {
            font-weight: bold;
            font-size: 18px;
            color: #0047ab;
            margin-top: 10px;
        }

        .role {
            font-size: 14px;
            color: #555;
            margin-bottom: 10px;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 11px;
            margin-top: 20px;
            position: relative;
        }

        .info-left,
        .info-right {
            width: 48%;
        }

        .info-left {
            text-align: right;
            padding-right: 10px;
        }

        .info-right {
            padding-left: 10px;
        }

        .info-left div,
        .info-right div {
            margin-bottom: 8px;
        }

        .label {
            font-weight: bold;
            color: #0047ab;
            font-size: 13px;
        }

        .value {
            color: #000;
            font-size: 12px;
        }

        .info-separator {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 50%;
            width: 1px;
            border-left: 1px dotted #aaa;
        }

        .qr {
            text-align: left;
            margin-top: 5px;
        }

        .qr img {
            width: 80px;
            height: 80px;
            border: 1px solid #eee;
            padding: 5px;
            background: white;
        }

        .footer {
            font-size: 10px;
            opacity: 0.6;
            text-align: left;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="controls">
        <button class="print-button" onclick="window.print()">Print All Cards</button>
    </div>

    <div class="card-container">
        @foreach ($users as $user)
            <div class="card">
                <div class="corner-circle top-left-circle"></div>
                <div class="corner-circle bottom-right-circle"></div>

                <div class="header">
                    <img src="{{ asset('default/logo.png') }}" class="header-logo" />

                    <div class="header-text">
                        <div class="header-title">CITIZENS<br>MOVEMENT<br>FOR CHANGE</div>
                        <div class="header-tagline">For the People, By the People</div>
                    </div>
                </div>

                <div class="profile">

                    @if ($user->avatar && file_exists(public_path($user->avatar)))
                        <img src="{{ asset($user->avatar) }}" alt="Member Photo" />
                    @else
                        <img src="{{ asset('default/profile.jpg') }}" alt="Member Photo" />
                    @endif



                    <div class="name">{{ $user->name }}</div>
                    <div class="role">{{ strtoupper($user->profile->type ?? 'MEMBER') }}</div>
                </div>

                <div class="info-section">
                    <div class="info-left">
                        <div>
                            <div class="label">D.O.B</div>
                            <div class="value">{{ \Carbon\Carbon::parse($user->profile->dob)->format('d/m/Y') }}</div>
                        </div>
                        <div>
                            <div class="label">Date issused</div>
                            <div class="value">{{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y') }}</div>
                        </div>
                        <div>
                            <div class="label">ID No.</div>
                            <div class="value">
                                {{ 'cmc_' . $user->id_number - 1 }}
                            </div>
                        </div>
                    </div>

                    <div class="info-separator"></div>

                    <div class="info-right">
                        <div>
                            <div class="label">Gender</div>
                            <div class="value">{{ ucfirst($user->profile->gender ?? 'N/A') }}</div>
                        </div>
                        <div class="qr">
                            {!! $user->qrCode !!}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</body>

</html>
