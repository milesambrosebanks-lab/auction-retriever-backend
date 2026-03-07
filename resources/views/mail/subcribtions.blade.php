<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Our Newsletter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
           //background: linear-gradient(180deg, #131762 0%, #764ba2 100%);
            color: #131762;
            padding: 40px 20px;
            text-align: center;
            border-radius: 8px 8px 10px 8px;
        }
        .content {
            background: #ffffff;
            padding: 40px 20px;
            /*border: 1px solid #e0e0e0;*/
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #999;
            font-size: 14px;
        }

        .benefits {
           // background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .benefits li {
            margin: 10px 0;
        }
        h1 {
            margin: 0;
            font-size: 28px;
        }
        h2 {
            color: #131762;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Our Newsletter! 🎉</h1>
            <p>You're now part of our community</p>
        </div>
        
        <div class="content">
            <h2>Hello there!</h2>
            
            <p>Thank you for subscribing to our newsletter. We're thrilled to have you on board!</p>
            
            <div class="benefits">
                <h3>Here's what you can expect:</h3>
                <ul>
                    <li>Weekly updates on latest news and trends</li>
                    <li>Exclusive content and offers</li>
                    <li>Early access to new features</li>
                    <li>Special discounts and promotions</li>
                    <li>Helpful tips and resources</li>
                </ul>
            </div>
            
            <p>To ensure you never miss an update, please add <strong>{{ config('mail.mailers.mailFrom')?? 'newsletter@example.com' }}</strong> to your contacts.</p>
            
            
            <p style="margin-top: 30px; font-size: 14px; color: #999;">
                If you didn't subscribe to our newsletter, you can 
                <a href="{{ route('api.subscriber.remove',$subscriber->email) ?? '#' }}">unsubscribe here</a>.
            </p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>
                <a href="{{ $privacyPolicyUrl ?? '#' }}">Privacy Policy</a> | 
                <a href="{{ $termsUrl ?? '#' }}">Terms of Service</a>
            </p>
        </div>
    </div>
</body>
</html>