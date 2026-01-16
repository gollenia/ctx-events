const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
	...defaultConfig,

	resolve: {
		...defaultConfig.resolve,
		alias: {
			...defaultConfig.resolve.alias,
			'@events/form': path.resolve(__dirname, 'src/editor/blocks/form/_shared'),
            '@events/details': path.resolve(__dirname, 'src/editor/blocks/details/_shared'),
			'@events/datatable': path.resolve(__dirname, 'src/shared/datatable'),
			'@events/adminfields': path.resolve(__dirname, 'src/shared/adminfields'),
			'@events/i18n': path.resolve(__dirname, 'src/shared/i18n'),
		},
	},

	entry: {
		admin: path.resolve(__dirname, 'src/admin/index.js'),
		frontend: path.resolve(__dirname, 'src/frontend/index.js'),
		editor: path.resolve(__dirname, 'src/editor/index.js'),
	},

	output: {
		...defaultConfig.output,
		filename: '[name].js',
		path: path.resolve(__dirname, 'build'),
	},
};
