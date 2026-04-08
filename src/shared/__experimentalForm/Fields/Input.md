# `Input`

Wrapper for native `<input>` types such as `text`, `email`, `url`, `date`, and `number`.

## Use when

- a native browser input is sufficient
- you need pattern, min/max, or autocomplete behavior

## Important props

- `type`
- `name`
- `label`
- `value`
- `onChange`
- `required`
- `min` / `max`
- `pattern`
- `autoComplete`
- `error`

## Notes

- `number` here means the real native number input.
- Field layout does not come from this component, but from `FormFields`.
- For sliders, do not use `Input type="number"`; use `Range` instead.
