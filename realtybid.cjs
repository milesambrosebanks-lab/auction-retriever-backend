const puppeteer = require('puppeteer');

const limit = process.argv[2] || 50;
const pageNum = process.argv[3] || 1;

(async () => {
    try {
        const browser = await puppeteer.launch({
            headless: false,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });

        const page = await browser.newPage();

        await page.setUserAgent(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36'
        );

        // আগে main site hit
        await page.goto('https://www.realtybid.com/', {
            waitUntil: 'networkidle2'
        });

        await new Promise(r => setTimeout(r, 5000));

        // 🔥 এবার API URL direct open
        const apiUrl = `https://api.realtybid.com/public/search/listings?upperLeftLat=72.7087158&lowerRightLat=15.7760139&upperLeftLong=-173.2992296&lowerRightLong=-66.3193754&pageSize=${limit}&currentPage=${pageNum}&TYPE=126,127,24,115,15,8,33,20&CATEGORYLIST=1,2,7,3,4,6,5,8&searchType=filter`;

        await page.goto(apiUrl, {
            waitUntil: 'networkidle2'
        });

        // response text নাও
        const body = await page.evaluate(() => document.body.innerText);

        console.log(body);

        await browser.close();

    } catch (err) {
        console.error("ERROR:", err.message);
        process.exit(1);
    }
})();
