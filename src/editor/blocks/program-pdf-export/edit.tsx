import { useBlockProps } from '@wordpress/block-editor';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import Inspector from './inspector';

type MonthlyPdfExportAttributes = {
	title: string;
	buttonText: string;
	exportMode: 'week' | 'month' | 'year';
	periodsAhead: number;
	showEmptyDays: boolean;
	category?: number;
	featuredCategory?: number;
};

type CategoryRecord = {
	id: number;
	name: string;
};

type CategoryOption = {
	label: string;
	value: number;
};

type EditProps = {
	attributes: MonthlyPdfExportAttributes;
	setAttributes: (attributes: Partial<MonthlyPdfExportAttributes>) => void;
};

export default function Edit({ attributes, setAttributes }: EditProps) {
	const blockProps = useBlockProps({
		className: 'ctx-program-pdf-export',
	});
	const selectedCategory = attributes.category ?? attributes.featuredCategory ?? 0;

	const categoryOptions = useSelect((select) => {
		const list = ((select(coreStore) as {
			getEntityRecords: (
				kind: string,
				name: string,
				query?: Record<string, unknown>,
			) => CategoryRecord[] | null;
		}).getEntityRecords('taxonomy', 'ctx-event-categories', {
			hide_empty: false,
			per_page: -1,
		}) ?? []) as CategoryRecord[];

		return [
			{ label: __('No featured category', 'ctx-events'), value: 0 },
			...list.map((category) => ({
				label: category.name,
				value: category.id,
			})),
		] as CategoryOption[];
	}, []);

	const monthOptions = Array.from({
		length: Math.max(1, Math.min(24, attributes.periodsAhead || 12)),
	}).map((_, index) => {
		const date = new Date();
		if (attributes.exportMode === 'week') {
			date.setDate(date.getDate() + index * 7);
			const endDate = new Date(date);
			endDate.setDate(endDate.getDate() + 6);
			return `${new Intl.DateTimeFormat(undefined, {
				day: '2-digit',
				month: '2-digit',
				year: 'numeric',
			}).format(date)} - ${new Intl.DateTimeFormat(undefined, {
				day: '2-digit',
				month: '2-digit',
				year: 'numeric',
			}).format(endDate)}`;
		}

		if (attributes.exportMode === 'year') {
			date.setFullYear(date.getFullYear() + index);
			return new Intl.DateTimeFormat(undefined, {
				year: 'numeric',
			}).format(date);
		}

		date.setMonth(date.getMonth() + index);
		return new Intl.DateTimeFormat(undefined, {
			month: 'long',
			year: 'numeric',
		}).format(date);
	});

	return (
		<div {...blockProps}>
			<Inspector
				attributes={{ ...attributes, category: selectedCategory }}
				categoryOptions={categoryOptions}
				setAttributes={setAttributes}
			/>
			<div className="ctx-program-pdf-export__preview">
				<div className="ctx-program-pdf-export__title">
					{attributes.title || __('Programm als PDF', 'ctx-events')}
				</div>
				<div className="ctx-program-pdf-export__controls">
					<select disabled>
						{monthOptions.map((label) => (
							<option key={label}>{label}</option>
						))}
					</select>
					<button type="button" className="ctx-program-pdf-export__button">
						{attributes.buttonText || __('PDF herunterladen', 'ctx-events')}
					</button>
				</div>
			</div>
		</div>
	);
}
