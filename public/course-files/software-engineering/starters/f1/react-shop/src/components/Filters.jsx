// TODO 10: the filter form. Every input is CONTROLLED: its value comes from a
// prop, and a change calls the matching on...Change prop with the new value.
// Props: branches, categories, branch, search, category, mode,
//        onBranchChange, onSearchChange, onCategoryChange, onModeChange
// Return <form className="filters" role="search" aria-label="Find products"> with
// four labelled fields (the tests find them by these label words):
//   "Collect at"    a <select> with one option per branch (value = code, text = "Nkwen, Bamenda")
//   "Search"        an <input type="search">
//   "Category"      a <select>: "All categories" (value "All") then one option per category
//   "Practice mode" a <select>: normal (Normal), slow (Slow network), error (Server error (500))
// Stop the form reloading the page: onSubmit={(e) => e.preventDefault()}
export default function Filters(props) {
  return <p>TODO 10: Filters ({Object.keys(props).length} props)</p>;
}
