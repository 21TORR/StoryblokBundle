vNext
=====

* (bug) Fix RateLimiter configuration for Storyblok's Content API to hopefully not exceed their rate limit.
* (improvement) Prevent automatic API redirect by sorting the query parameters pre-emptively.



1.3.0
=====

* (feature) Add support for fetching a Story directly via their Uuid from the Storyblok Content API. 
* (feature) Add `StoryReferenceData`. 
* (bc) The `StoryChoices` no longer return the Uuid(s) of the referenced Stories. Instead, it returns the `StoryReferenceData` object(s).
* (feature) Add support for specifying a data mode key for the `StoryChoices` instance, which will be passed down to the corresponding `*StoryNormalizer`, which can conditionally return different data based on the mode key.


1.2.1
=====

* (bug) Fix invalid handling of `AbstractGroupingElement` fields.
* (bug) Don't crash when `CompositeField` field data is not present (for `allowMissingData` cases).



1.2.0
=====

* (bug) Fix invalid handling of nested fields.
* (feature) Add `CompositeField` to allow logical grouping of multiple fields.



1.1.1
=====

* (bug) Fix invalid handling of multi-select `ChoiceField`.



1.1.0
=====

* (feature) Add preview info for `ComponentData`.



1.0.0
=====

*   (feature) Initial Release
