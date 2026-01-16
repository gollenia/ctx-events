import { useSelect } from '@wordpress/data';
import { ComboboxControl, Spinner } from '@wordpress/components';

const PageSelect = ({ label, value, onChange, help }) => {
    const { pages, isResolving } = useSelect((select) => {
        const query = { per_page: 100, orderby: 'title', order: 'asc' };
        return {
            pages: select('core').getEntityRecords('postType', 'page', query),
            isResolving: select('core').isResolving('getEntityRecords', ['postType', 'page', query]),
        };
    }, []);

    if (isResolving) {
        return <Spinner />; 
    }

    const options = (pages || []).map((page) => ({
        label: page.title.rendered || '(Ohne Titel)',
        value: page.id,
    }));

    return (
        <ComboboxControl
            label={label}
            value={value}
            onChange={onChange}
            help={help}
            options={options}
        />
    );
};

export default PageSelect;
