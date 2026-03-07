<p>Hi {{ $donation->first_name ?? '' }} {{ $donation->last_name ?? '' }},</p>

<p>This is a friendly reminder that your monthly donation is due on {{ $donation->amount }}.</p>

<p>Thank you for your continued support.</p>

<p>The Citizens Movement for Change</p>
