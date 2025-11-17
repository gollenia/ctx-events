import { useEffect, useRef } from '@wordpress/element';

const useDebouncedEffect = (effect, deps, delay = 300) => {
	const timeout = useRef();
	useEffect(() => {
		clearTimeout(timeout.current);
		timeout.current = setTimeout(effect, delay);
		return () => clearTimeout(timeout.current);
	}, deps);
};

export default useDebouncedEffect;
