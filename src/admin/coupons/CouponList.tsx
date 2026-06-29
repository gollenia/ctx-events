import DataTable from '@events/datatable/DataTable';
import type { DataFilterField, DataViewConfig } from '@events/datatable/types';
import apiFetch from '@wordpress/api-fetch';
import { SnackbarList } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { store as noticesStore } from '@wordpress/notices';
import { copyTextToClipboard } from '../shared/copyTextToClipboard';
import actions from './actions';
import { couponStatusItems } from './couponStatusItems';
import { createFields } from './fields';
import { useFetchCoupons } from './useFetchCoupons';

const CouponList = () => {
	const { createSuccessNotice, createWarningNotice, removeNotice } =
		useDispatch(noticesStore);
	const notices = useSelect((select) => select(noticesStore).getNotices(), []);
	const [view, setView] = useState<DataViewConfig>({
		search: '',
		page: 1,
		perPage: 20,
		sort: { field: 'date', direction: 'desc' },
		filters: [{ field: 'status', operator: 'is', value: 'publish' }] as Array<DataFilterField>,
		titleField: 'title',
		fields: ['title', 'code', 'discount', 'usage', 'expiresAt', 'status'] as Array<string>,
	});
	const fields = createFields({
		onCodeCopy: (code) => {
			void copyTextToClipboard(code).then((copied) => {
				if (copied) {
					createSuccessNotice(__('Coupon code copied to clipboard.', 'ctx-events'), {
						type: 'snackbar',
					});
					return;
				}

				createWarningNotice(__('The coupon code could not be copied.', 'ctx-events'), {
					type: 'snackbar',
				});
			});
		},
	});

	const { coupons, loading, statusItems, pagination } = useFetchCoupons(view);

	const handleViewChange = (updates: Partial<DataViewConfig>) => {
		setView((prev) => {
			const nextView = { ...prev, ...updates };
			if (updates.filters || updates.search !== undefined) {
				nextView.page = 1;
			}

			return nextView;
		});
	};

	const handleExport = () => {
		const params = new URLSearchParams({
			order_by: view.sort?.field ?? 'date',
			order: view.sort?.direction ?? 'desc',
			search: view.search || '',
		});
		const status = view.filters?.find((filter) => filter.field === 'status')?.value;
		if (status !== undefined && status !== '') {
			params.set('status', String(status));
		}

		void apiFetch({
			path: `/events/v3/coupons/export?${params.toString()}`,
			method: 'GET',
			parse: false,
		})
			.then(async (response) => {
				if (!(response instanceof Response) || !response.ok) {
					let errorMessage = __('The export could not be downloaded.', 'ctx-events');

					if (response instanceof Response) {
						const contentType = response.headers.get('content-type') ?? '';
						if (contentType.includes('application/json')) {
							const payload = (await response.json()) as {
								message?: string;
								code?: string;
							};
							errorMessage = payload.message ?? payload.code ?? errorMessage;
						} else {
							const text = await response.text();
							if (text.trim() !== '') {
								errorMessage = text;
							}
						}
					}

					throw new Error(errorMessage);
				}

				const blob = await response.blob();
				const disposition = response.headers.get('content-disposition');
				const fileNameMatch = disposition?.match(/filename="?([^"]+)"?/i);
				const fileName = fileNameMatch?.[1] ?? 'coupons-export.xlsx';
				const objectUrl = window.URL.createObjectURL(blob);
				const anchor = document.createElement('a');
				anchor.href = objectUrl;
				anchor.download = fileName;
				document.body.append(anchor);
				anchor.click();
				anchor.remove();
				window.URL.revokeObjectURL(objectUrl);
			})
			.catch((error: unknown) => {
				const message = (() => {
					if (
						typeof error === 'object' &&
						error !== null &&
						'message' in error &&
						typeof error.message === 'string' &&
						error.message.trim() !== ''
					) {
						const code =
							'code' in error && typeof error.code === 'string'
								? error.code
								: null;
						const status =
							'data' in error &&
							typeof error.data === 'object' &&
							error.data !== null &&
							'status' in error.data &&
							typeof error.data.status === 'number'
								? error.data.status
								: null;

						if (code && status) {
							return `${error.message} (${code}, ${status})`;
						}

						if (code) {
							return `${error.message} (${code})`;
						}

						return error.message;
					}

					return __(
						'The export could not be downloaded. Please check your permissions and try again.',
						'ctx-events',
					);
				})();
				window.alert(message);
			});
	};

	return (
		<DataTable
			data={coupons}
			fields={fields}
			view={view}
			actions={actions}
			search={true}
			onChangeView={handleViewChange}
			paginationInfo={pagination}
			isLoading={loading}
			searchLabel={__('Search coupons...', 'ctx-events')}
			availableStatusItems={couponStatusItems(statusItems)}
			title={__('Coupons', 'ctx-events')}
			createLink="/wp-admin/post-new.php?post_type=ctx-event-coupon"
			createLinkLabel={__('New Coupon', 'ctx-events')}
		>
			<h1 className="wp-heading-inline">{__('Coupons', 'ctx-events')}</h1>

			<a
				href="/wp-admin/post-new.php?post_type=ctx-event-coupon"
				className="page-title-action"
			>
				{__('New Coupon', 'ctx-events')}
			</a>
			<button
				type="button"
				className="page-title-action"
				onClick={handleExport}
			>
				{__('Export', 'ctx-events')}
			</button>

			<hr className="wp-header-end" />

			<DataTable.StatusSelect />
			<SnackbarList notices={notices} onRemove={removeNotice} />
			<DataTable.Filter />
			<DataTable.Table />
			<DataTable.Pagination />
		</DataTable>
	);
};

export default CouponList;
