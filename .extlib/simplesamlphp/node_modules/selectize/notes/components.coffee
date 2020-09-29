# Can suggest entities based on partial search,
# and highlight relevant parts in suggestions
Autocomplete =
  options:
    dataSource:
    onSuggest: (suggestions) ->

  feed: (input) ->


# Displays suggestion DOM elements under an input element.
# Elements can be focused and selected.
Dropdown =
  options:
    renderItem: (item) ->
    onSelect: (item) ->
    onFocus: (item) ->

  open: (maybeList) ->
  close: ->

  # private
  _populate: (list) ->

# A DropdownUngroupedList is an array of Objects
# A DropdownGroupedList is an array of Groups, each Group has a label and
# contains elements

canada = [
  {
    label: "Provinces",
    elements: [
      "British Columbia",
      "Alberta",
      "Saskatchewan",
      "Manitoba",
      "Ontario",
      "Québec",
      "New Brunswick",
      "Nova Scotia",
      "Prince Edward Island",
      "Newfoundland and Labrador"
    ]
  },
  {
    label: "Territories",
    elements: [
      "Yukon",
      "Northwest Territories",
      "Nunavut"
    ]
  }
]

# Behaves like a text input, can contain items (elements, eg
# inline-blocks, tags) as well as text. Knows how to serialize its
# contents into a string value for submission.
ItemInput =
  options:
    onType: (char, value) ->

  add: (item) ->
  value:

SelectizeElement =
  setup: ->
    input.on 'type', autocomplete.feed
    autocomplete.on 'suggest', dropdown.open
    dropdown.on 'select', input.add
