import { __ } from '@wordpress/i18n';
import type { DataPaginationInfo } from './types';

interface PaginationProps {
	paginationInfo: DataPaginationInfo;
	page: number;
	onPageChange: (page: number) => void;
}

const Pagination = ({
	paginationInfo,
	onPageChange,
	page,
}: PaginationProps) => {
	const isFirstPage = page === 1;
	const isLastPage = page === paginationInfo.totalPages;

	return (
		<div className="tablenav bottom">
			<div className="tablenav-pages">
				<span className="displaying-num">
					{paginationInfo?.totalItems} {__('items', 'ctx-events')}
				</span>
				<span className="pagination-links">
					<button
						type="button"
						className={`tablenav-pages-navspan button ${isFirstPage ? 'disabled' : ''}`}
						onClick={() => onPageChange(1)}
						disabled={isFirstPage}
					>
						«
					</button>
					<button
						type="button"
						className={`tablenav-pages-navspan button ${isFirstPage ? 'disabled' : ''}`}
						onClick={() => onPageChange(page - 1)}
						disabled={isFirstPage}
					>
						‹
					</button>

					<span className="screen-reader-text">
						{__('Current page', 'ctx-events')}
					</span>
					<span id="table-paging" className="paging-input">
						<span className="tablenav-paging-text">
							{' '}
							{page} {__('of', 'ctx-events')}{' '}
							<span className="total-pages">{paginationInfo?.totalPages}</span>
						</span>
					</span>

					<button
						type="button"
						className={`next-page button ${isLastPage ? 'disabled' : ''}`}
						onClick={() => onPageChange(page + 1)}
						disabled={isLastPage}
					>
						›
					</button>
					<button
						type="button"
						className={`last-page button ${isLastPage ? 'disabled' : ''}`}
						onClick={() => onPageChange(paginationInfo?.totalPages || 1)}
						disabled={isLastPage}
					>
						»
					</button>
				</span>
			</div>
			<br className="clear" />
		</div>
	);
};

export default Pagination;
export type { PaginationProps };
