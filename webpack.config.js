// webpack.config.js
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
	// Basis-Konfiguration von wp-scripts übernehmen
	...defaultConfig,

	resolve: {
		...defaultConfig.resolve,
		alias: {
			...defaultConfig.resolve.alias,
			// Hier definierst du deine Aliases
            // Passt den Pfad an deine Struktur an (src/editor/blocks...)
			'@events/form': path.resolve(__dirname, 'src/editor/blocks/form/_shared'),
            
            // Mapping für Details-Helper
            '@events/details': path.resolve(__dirname, 'src/editor/blocks/details/_shared'),
		},
	},

	// Eigene Entry-Points definieren
	entry: {
		admin: path.resolve(__dirname, 'src/admin/index.js'),
		frontend: path.resolve(__dirname, 'src/frontend/index.js'),
		editor: path.resolve(__dirname, 'src/editor/index.js'),
	},

	output: {
		...defaultConfig.output,
		// Name anhand des Entry-Keys
		filename: '[name].js',
		path: path.resolve(__dirname, 'build'),
	},
};
