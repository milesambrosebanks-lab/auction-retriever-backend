<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Membership Cards | Citizens Movement for Change</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: white;
      margin: 0;
      padding: 10mm;
    }

    .sheet {
      width: 210mm;
      height: 297mm;
      box-sizing: border-box;
    }

    .cards-table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
    }

    .cards-table td {
      vertical-align: top;
      padding: 10px;
      width: 50%;
    }

    .card {
      width: 300px;
      height: 480px;
      background-color: white;
      border: 1px solid #ccc;
      border-radius: 12px;
      padding: 20px;
      box-sizing: border-box;
      position: relative;
      margin: 0 auto;
      /* border: #0047ab 2px solid; */
    }

    .card.front {
      position: relative;
      overflow: hidden;
    }

    /* Top-left red corner */
    .card.front::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 60px;
      height: 60px;
      background-color: red;
      clip-path: polygon(0 0, 100% 0, 0 100%);
    }

    /* Bottom-right blue corner */
    .card.front::after {
      content: '';
      position: absolute;
      bottom: -10;
      right: 0;
      width: 50px;
      height: 50px;
      background-color: #0047ab;
      /* CMC blue */
      clip-path: polygon(100% 100%, 0 100%, 100% 0);
      /* border-radius: 100px 0 0 0; */
      transform: rotate(120deg);

      
    }

    /* Header Table */
    .header-table {
      margin: 0 auto 10px auto;
      border-collapse: collapse;
    }

    .header-table td {
      vertical-align: middle;
    }

    .header-logo {
      width: 65px;
      height: 65px;
    }

    .header-title {
      font-size: 14px;
      font-weight: bold;
      color: #0047ab;
      line-height: 1.1;
    }

    .header-tagline {
      font-size: 9px;
      font-style: italic;
      color: #333;
      margin-top: 2px;
    }

    /* Profile */
    .profile {
      text-align: center;
      margin-top: 10px;
      margin-bottom: 15px;
    }

    .profile img {
      width: 90px;
      height: 90px;
      object-fit: cover;
      border-radius: 5px;
    }

    .name {
      font-weight: bold;
      font-size: 18px;
      color: #0047ab;
      margin-top: 10px;
    }

    .role {
      font-size: 13px;
      color: #555;
      margin-bottom: 10px;
    }

    /* Info Table */
    .info-table {
      width: 100%;
      font-size: 11px;
      border-collapse: collapse;
      margin-top: 10px;
    }

    .info-table td {
      vertical-align: top;
      padding: 0 10px;
    }

    .info-left {
      text-align: right;
      width: 48%;
    }

    .info-right {
      text-align: left;
      width: 48%;
    }

    .separator {
      width: 4%;
      border-left: 1px dotted #aaa;
    }

    .label {
      font-weight: bold;
      color: #0047ab;
      margin-bottom: 2px;
    }

    .qr img {
      width: 70px;
      height: 70px;
      margin-top: 8px;
    }

    /* Footer */
    .footer {
      font-size: 10px;
      opacity: 0.6;
      text-align: left;
      margin-top: 10px;
    }

    /* Back Card */
    .back .org-name {
      text-align: center;
      font-size: 13px;
      margin-bottom: 5px;
    }

    .back-title {
      text-align: center;
      font-size: 14px;
      font-weight: bold;
      background-color: #0047ab;
      color: white;
      padding: 10px;
      margin-bottom: 10px;
      border-radius: 6px;
    }

    .seal {
      display: block;
      margin: 0 auto 20px auto;
      width: 200px;
      height: 200px;
      object-fit: contain;
    }

    .notice {
      font-size: 12px;
      color: #333;
      text-align: center;
      padding: 0 10px;
      margin-bottom: 15px;
    }

    .back-footer {
      background-color: #0047ab;
      padding: 10px;
      border-radius: 6px;
      text-align: center;
      color: white;
    }

    .footer-header {
      display: inline-block;
    }

    .footer-logo {
      width: 50px;
      height: 50px;
      vertical-align: middle;
      margin-right: 10px;
    }

    .footer-text {
      display: inline-block;
      vertical-align: middle;
      text-align: left;
    }

    .footer-title {
      font-size: 12px;
      font-weight: bold;
      line-height: 1.1;
    }

    .footer-tagline {
      font-size: 9px;
      font-style: italic;
      margin-top: 2px;
    }
  </style>
</head>

<body>
  <div class="sheet">
    <table class="cards-table">
      <tr>
        <td>
          <!-- FRONT SIDE - Card 1 -->
          <div class="card front" style="box-shadow: 0 0 5px 0 black;">
            <div class="header">
              <table class="header-table">
                <tr>
                  <td>
                    <img src="data:image/png;base64,{{ $logoBase64 }}" alt="CMC Logo" class="header-logo" />
                  </td>
                  <td style="padding-left: 10px;">
                    <div class="header-title">
                      CITIZENS<br />MOVEMENT<br />FOR CHANGE
                    </div>
                    <div class="header-tagline">For the People, By the People</div>
                  </td>
                </tr>
              </table>
            </div>
            <div class="profile">
              <img src="data:image/jpeg;base64,{{ $avatarBase64 }}" alt="Member Photo" />
              <div class="name">{{ $user->name }}</div>
              <div class="role">{{ $user->profile->type }}</div>
            </div>
            <table class="info-table">
              <tr>
                <td class="info-left">
                  <div>
                    <div class="label">D.O.B</div>
                    <div>{{ \Carbon\Carbon::parse($user->dob)->format('d/m/Y') }}</div>
                  </div>
                  <div style="margin-top: 10px;">
                    <div class="label">Date Issued</div>
                    <div>{{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y') }}</div>
                  </div>
                  <div style="margin-top: 10px;">
                    <div class="label">ID No.</div>
                    <div>{{ $user->slug }}</div>
                  </div>
                </td>
                <td class="separator"></td>
                <td class="info-right">
                  <div>
                    <div class="label">Gender</div>
                    <div>{{ ucfirst($user->profile->gender) }}</div>
                  </div>
                  <div class="qr">
                    <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code" />
                  </div>
                </td>
              </tr>
            </table>
            <div class="footer">ID No. {{ $user->slug }}</div>
          </div>
        </td>
        <td>
          <!-- BACK SIDE - Card 2 -->
          <div class="card back">
            <div class="org-name">Republic of Liberia</div>
            <div class="back-title">MEMBERSHIP ID CARD</div>
            <img src="data:image/png;base64,{{ $backLogoBase64 }}" alt="Liberia Seal" class="seal" />
            <div class="notice">
              If found, please return to the nearest police station or any Citizens Movement for Change (CMC) office.
            </div>
            <div class="back-footer">
              <div class="footer-header">
                <img src="data:image/png;base64,{{ $whitelogoBase64 }}" alt="CMC Logo" class="footer-logo" />
                <div class="footer-text">
                  <div class="footer-title">
                    CITIZENS<br />MOVEMENT<br />FOR CHANGE
                  </div>
                  <div class="footer-tagline">For the People, By the People</div>
                </div>
              </div>
            </div>
          </div>
        </td>
      </tr>
    </table>
  </div>
</body>

</html>