<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Property Listings</title>
</head>

<body style="margin:0; padding:0; font-family: 'Segoe UI', Arial, sans-serif; background-color:#f0f2f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f2f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden;">
                    <!-- Header -->
                    <tr>
                        <td style="background-color:#ffffff; padding: 40px 30px; border-bottom: 2px solid #eaeef2;">
                            <h1 style="margin:0; color:#1a2634; font-size: 28px; font-weight: 600; letter-spacing: -0.5px;">🏠 New Auction Listings</h1>
                            <p style="margin:10px 0 0; color:#4a5b6e; font-size: 16px;">Discover this week's hottest Auctio</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin:0 0 25px; color:#4a5568; font-size: 16px; line-height: 1.6;">Hello,</p>
                            <p style="margin:0 0 30px; color:#4a5568; font-size: 16px; line-height: 1.6;">Check out these amazing properties up for auction this week. Don't miss your chance to bid!</p>
                            
                            <!-- Table Container -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: separate; border-spacing: 0; background-color:#ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                                <!-- Table Header -->
                                <tr>
                                    <td style="background-color:#f8fafc; padding: 15px 12px; border-bottom: 2px solid #e2e8f0;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <th style="color:#2d3748; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; padding: 0 8px;">Title</th>
                                                <th style="color:#2d3748; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; padding: 0 8px;">County</th>
                                                <th style="color:#2d3748; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; padding: 0 8px;">State</th>
                                                <th style="color:#2d3748; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; padding: 0 8px;">Starting Bid</th>
                                                <th style="color:#2d3748; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; padding: 0 8px;">Auction Date</th>
                                                <th style="color:#2d3748; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; padding: 0 8px;">Source</th>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                
                                <!-- Table Body -->
                                @foreach ($listings as $index => $listing)
                                <tr>
                                    <td style="padding: 0; border-bottom: {{ !$loop->last ? '1px solid #e2e8f0' : 'none' }};">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding: 16px 12px; color:#1a202c; font-size: 14px;">{{ $listing->title }}</td>
                                                <td style="padding: 16px 12px; color:#4a5568; font-size: 14px;">{{ $listing->country }}</td>
                                                <td style="padding: 16px 12px; color:#4a5568; font-size: 14px;">{{ $listing->state }}</td>
                                                <td style="padding: 16px 12px; color:#2c3e50; font-size: 14px;">${{ $listing->starting_bid }}</td>
                                                <td style="padding: 16px 12px; color:#4a5568; font-size: 14px;">{{ $listing->auction_date }}</td>
                                                <td style="padding: 16px 12px;">
                                                    <a href="{{ $listing->source_link }}" style="display: inline-block; background-color:#2c3e50; color:#ffffff; text-decoration: none; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 500;">View→</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                            
                            <!-- Footer Note -->
                            <p style="margin: 30px 0 0; color:#718096; font-size: 14px; line-height: 1.6; text-align: center;">
                                Don't wait too long - these properties won't last!<br>
                                <span style="font-size: 13px; color:#a0aec0;">You're receiving this because you subscribed to property alerts.</span>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f8fafc; padding: 30px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="margin:0 0 10px; color:#718096; font-size: 14px;">Happy Bidding! </p>
                            <p style="margin:0; color:#a0aec0; font-size: 12px;">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                            {{-- <p style="margin:10px 0 0;">
                                <a href="#" style="color:#2c3e50; text-decoration: none; font-size: 12px; margin:0 10px;">Unsubscribe</a>
                                <span style="color:#cbd5e0;">|</span>
                                <a href="#" style="color:#2c3e50; text-decoration: none; font-size: 12px; margin:0 10px;">Privacy Policy</a>
                            </p> --}}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>