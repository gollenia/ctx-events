# `Country`

Country picker built on top of `Combobox`.

## Use when

- ISO country codes should be stored
- localized country names should be shown in the UI

## Important props

- `value`
- `onChange`
- `region`
- `required`
- `label`

## Notes

- Externally, the field works with country codes such as `AT` or `DE`.
- Internally, the user sees localized country names.
- The region filter is implemented via `countries.ts`.
