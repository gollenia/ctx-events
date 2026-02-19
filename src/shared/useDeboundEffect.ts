import { useEffect, useRef } from '@wordpress/element';

const useDebouncedEffect = (effect: () => void, deps: any[], delay = 300) => {
    const timeout = useRef<ReturnType<typeof setTimeout> | undefined>(undefined);

    useEffect(() => {
        if (timeout.current) {
            clearTimeout(timeout.current);
        }

        timeout.current = setTimeout(effect, delay);

        return () => {
            if (timeout.current) {
                clearTimeout(timeout.current);
            }
        };
    }, deps);
};

export default useDebouncedEffect;