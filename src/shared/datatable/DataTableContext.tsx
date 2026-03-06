import { createContext, useContext } from '@wordpress/element';
import type { DataTableProps } from './DataTable';

interface DataTableContextType
	extends Omit<DataTableProps<unknown>, 'children'> {
	screenMetaContext: string;
	setScreenMetaContext: (context: string) => void;
}

export const DataTableContext = createContext<DataTableContextType | null>(
	null,
);

export const useDataTable = () => {
	const context = useContext(DataTableContext);
	if (!context) throw new Error('useDataTable must be used within DataTable');
	return context;
};
