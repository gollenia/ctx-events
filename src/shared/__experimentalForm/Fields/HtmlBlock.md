# `HtmlBlock`

Sanitized HTML inside a form.

## Use when

- editorial content should be rendered between fields
- you need hints, explanations, or structural text

## Important props

- `content`
- `role`

## Notes

- HTML is sanitized via `sanitizeHtml` before rendering.
- This is not an input field, but a content block inside the form flow.
