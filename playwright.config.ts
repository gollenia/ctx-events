import fs from 'node:fs';
import path from 'node:path';
import { defineConfig } from '@playwright/test';

const localEnvPath = path.resolve(__dirname, '.env.playwright.local');

if (fs.existsSync(localEnvPath)) {
	const lines = fs.readFileSync(localEnvPath, 'utf8').split(/\r?\n/);

	for (const line of lines) {
		const trimmed = line.trim();

		if (!trimmed || trimmed.startsWith('#')) {
			continue;
		}

		const separatorIndex = trimmed.indexOf('=');

		if (separatorIndex === -1) {
			continue;
		}

		const key = trimmed.slice(0, separatorIndex).trim();
		const value = trimmed.slice(separatorIndex + 1).trim();

		if (key && process.env[key] === undefined) {
			process.env[key] = value;
		}
	}
}

export default defineConfig({
	testDir: './tests/e2e',
	use: {
		baseURL: process.env.PLAYWRIGHT_BASE_URL,
		headless: true,
		screenshot: 'on',
		video: 'retain-on-failure',
		trace: 'retain-on-failure',
	},
});
