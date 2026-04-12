import puppeteer from 'puppeteer';

async function scrapeAuctions(limit = 50, offset = 0) {
    let browser;
    try {
        browser = await puppeteer.launch({
            headless: 'new',
            executablePath: '/usr/bin/chromium-browser',
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',

                '--disable-gpu',
                '--no-zygote',
                '--single-process',

            ]
        });

        const page = await browser.newPage();

        // Set viewport
        await page.setViewport({ width: 1920, height: 1080 });

        // Make the GraphQL request directly via page.evaluate
        await page.goto('https://www.auction.com', { waitUntil: 'networkidle2' });

        // Execute fetch directly in browser context (bypasses Cloudflare better)
        const result = await page.evaluate(async (limit, offset) => {
            const listingsQuery = `query resiSearch($filters: ListingCompatabilityFilters!) {
        seek_listings_from_filters(filters: $filters) {
          total_count
          content {
            ... on Listing {
              listing_id
              listing_status
              primary_photo
              listing_page_path
              formatted_address(format: DOUBLE_LINE)
              auction {
                start_date
                end_date
                starting_bid
              }
              primary_property {
                summary {
                  structure_type_code
                  structure_type_group
                }
              }
            }
          }
        }
      }`;

            const detailsQuery = `query ($listingIds: [ID!]!) {
        online_segments_batched(filters: { listing_ids: $listingIds }) {
          listing_id
          start_date
          initial_end_date
          starting_bid_amount
          segment_status
        }
      }`;

            const listResp = await fetch('https://graph.auction.com/graphql', {
                method: 'POST',
                headers: {
                    'accept': 'application/json',
                    'content-type': 'application/json',
                    'origin': 'https://www.auction.com',
                    'referer': 'https://www.auction.com/',
                    'auction-graph-source': 'auctioncom',
                },
                body: JSON.stringify({
                    query: listingsQuery,
                    variables: {
                        filters: {
                            listing_type: 'active',
                            usecode_property_type: 'resi_sfr',
                            sort: 'auction_date_order,resi_sort_v2',
                            limit,
                            offset,
                            version: 1,
                        }
                    }
                })
            });

            if (!listResp.ok) {
                return { error: `LISTINGS HTTP ${listResp.status}: ${listResp.statusText}` };
            }

            const listData = await listResp.json();
            const ids = (listData?.data?.seek_listings_from_filters?.content ?? []).map(i => i.listing_id);

            let detailData = { data: { online_segments_batched: [] } };

            if (ids.length > 0) {
                const detailResp = await fetch('https://graph.auction.com/graphql', {
                    method: 'POST',
                    headers: {
                        'accept': 'application/json',
                        'content-type': 'application/json',
                        'origin': 'https://www.auction.com',
                        'referer': 'https://www.auction.com/',
                        'auction-graph-source': 'auctioncom',
                    },
                    body: JSON.stringify({
                        query: detailsQuery,
                        variables: { listingIds: ids }
                    })
                });

                if (detailResp.ok) {
                    detailData = await detailResp.json();
                } else {
                    detailData = { error: `DETAILS HTTP ${detailResp.status}: ${detailResp.statusText}` };
                }
            }

            return { listings: listData, details: detailData };
        }, limit, offset);

        await browser.close();
        return result;

    } catch (error) {
        if (browser) await browser.close();
        throw error;
    }
}

// Main execution
const limit = parseInt(process.argv[2]) || 50;
const offset = parseInt(process.argv[3]) || 0;

scrapeAuctions(limit, offset)
    .then(data => {
        if (data.data && data.data.seek_listings_from_filters) {
            const content = data.data.seek_listings_from_filters.content;
            console.error(`[DEBUG] Successfully fetched ${content.length} listings`);
            content.slice(0, 3).forEach((item, idx) => {
                console.error(`[DEBUG] Item ${idx + 1}: ID=${item.listing_id}, Address=${item.formatted_address?.join(' | ')}`);
            });
        }
        console.log(JSON.stringify(data, null, 2));
        process.exit(0);
    })
    .catch(err => {
        console.error('ERROR:', err.message);
        process.exit(1);
    });
