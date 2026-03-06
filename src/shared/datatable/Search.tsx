import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { DataViewConfig } from './types';

interface SearchProps {
	view: DataViewConfig;
	onChangeView: (updates: Partial<DataViewConfig>) => void;
}

const Search = ({ view, onChangeView }: SearchProps) => {
	const [search, setSearch] = useState(view.search || '');

	const onSubmit = (e: React.FormEvent) => {
		e.preventDefault();
		onChangeView({ search });
	};

	return (
		<form onSubmit={onSubmit}>
			<p className="search-box">
				<label className="screen-reader-text" htmlFor="post-search-input">
					{__('Search Items', 'ctx-events')}
				</label>
				<input
					type="search"
					id="post-search-input"
					name="s"
					value={search}
					onChange={(e) => setSearch(e.target.value)}
				/>
				<input
					type="submit"
					id="search-submit"
					className="button"
					onClick={onSubmit}
					value={__('Search Items', 'ctx-events')}
				/>
			</p>
		</form>
	);
};

export default Search;
export type { SearchProps };
