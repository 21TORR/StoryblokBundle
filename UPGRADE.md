5.x to 6.0
==========

* Update calls to `ManagementApi::updateStory()` to not pass a json encoded string anymore, pass as array directly.
  

3.x to 5.0
==========

* The Storyblok webhook URL has changed, it now contains the storyblok adapter key.
* Removed `storyblok:components:overview` command, use `storyblok:debug` instead.
* Removed `ContentApi`, `ManagementApi`, `StoryblokIdSlugMapper` and `RequestValidator` services.
* Added `AbstractStoryblokAdapter` to wrap a connection to a storyblok space. You can now have multiple connections to multiple spaces now.
* All services that use the previously global services now need either the space id (to get the adapter) or the adapter directly.
* Removed the global storyblok bundle config. You now instead need to create the `StoryblokConfig` inside your adapter yourself.


2.x to 5.0
==========

* `AbstractField::enablePreview()` is removed, use `AbstractField::useAsAdminDisplayName()` instead.


2.x to 3.0
==========

- To non-interactively sync components via the CLI, you now need to add the `--force` option.
- `ComponentsWithTag` was removed in favor of `ComponentFilter`.
- Removed the functionality to filter components by component group. Use tags instead.
