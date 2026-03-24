const { test, expect } = require('@playwright/test');

test('homepage loads', async ({ page, baseURL }) => {
	test.skip(!baseURL, 'Set PLAYWRIGHT_BASE_URL to run Playwright tests.');

	await page.goto('/');
	await expect(page).toHaveTitle(/./);
});
