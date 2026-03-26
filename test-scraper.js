import puppeteer from 'puppeteer';

async function testQuick() {
  let browser;
  try {
    console.log('⏳ Launching browser...');
    browser = await puppeteer.launch({
      headless: 'new',
      args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
    });

    const page = await browser.newPage();
    console.log('⏳ Going to auction.com...');
    await page.goto('https://www.auction.com', { waitUntil: 'networkidle2', timeout: 60000 });

    console.log('⏳ Fetching GraphQL data...');
    const result = await page.evaluate(async () => {
      const response = await fetch('https://graph.auction.com/graphql', {
        method: 'POST',
        headers: {
          'accept': 'application/json',
          'content-type': 'application/json',
          'origin': 'https://www.auction.com',
          'referer': 'https://www.auction.com/',
          'auction-graph-source': 'auctioncom',
        },
        body: JSON.stringify({
          query: `query resiSearch($filters: ListingCompatabilityFilters!) {
            seek_listings_from_filters(filters: $filters) {
              total_count
              content {
                ... on Listing {
                  listing_id
                  listing_status
                  formatted_address(format: DOUBLE_LINE)
                  listing_page_path
                }
              }
            }
          }`,
          variables: {
            filters: {
              listing_type: 'active',
              usecode_property_type: 'resi_sfr',
              sort: 'auction_date_order,resi_sort_v2',
              limit: 1,
              offset: 0,
              version: 1,
            }
          }
        })
      });

      return response.json();
    });

    await browser.close();

    if (result.data?.seek_listings_from_filters?.content) {
      const items = result.data.seek_listings_from_filters.content;
      console.log(`✅ SUCCESS! Fetched ${items.length} listing(s)`);
      items.forEach((item, idx) => {
        console.log(`\n[Listing ${idx + 1}]`);
        console.log(`  ID: ${item.listing_id}`);
        console.log(`  Status: ${item.listing_status}`);
        console.log(`  Address: ${item.formatted_address.join(' | ')}`);
        console.log(`  URL: ${item.listing_page_path}`);
      });
    } else {
      console.log('❌ No data in response');
      console.log(JSON.stringify(result, null, 2));
    }

  } catch (error) {
    console.error('❌ ERROR:', error.message);
    if (browser) await browser.close();
    process.exit(1);
  }
}

testQuick();
