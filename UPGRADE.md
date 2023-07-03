2.x to 4.0
==========


* `AbstractField::enablePreview()` is removed, use `AbstractField::useAsAdminDisplayName()` instead.


2.x to 3.0
==========

- To non-interactively sync components via the CLI, you now need to add the `--force` option.
- `ComponentsWithTag` was removed in favor of `ComponentFilter`.
- Removed the functionality to filter components by component group. Use tags instead.
