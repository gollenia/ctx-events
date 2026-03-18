const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
	...defaultConfig,

	resolve: {
		...defaultConfig.resolve,
		alias: {
			...defaultConfig.resolve.alias,
			'@events/form': path.resolve(__dirname, 'src/editor/blocks/form/_shared'),
			'@events/details': path.resolve(
				__dirname,
				'src/editor/blocks/details/_shared',
			),
			'@events/datatable': path.resolve(__dirname, 'src/shared/datatable'),
			'@events/adminfields': path.resolve(__dirname, 'src/shared/adminfields'),
			'@events/i18n': path.resolve(__dirname, 'src/shared/i18n'),
			'@events/utilities': path.resolve(__dirname, 'src/shared/utilities'),
		},
	},

	entry: {
		admin: path.resolve(__dirname, 'src/admin/index.ts'),
		frontend: path.resolve(__dirname, 'src/frontend/index.ts'),
		editor: path.resolve(__dirname, 'src/editor/index.ts'),
	},

	output: {
		...defaultConfig.output,
		filename: '[name].js',
		path: path.resolve(__dirname, 'build'),
	},
};
