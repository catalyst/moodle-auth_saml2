// what are the i18n conventions in js?
// what strings do we need to translate
// is i18n lang set?

describe("I18n", function(){
	describe("dictionary syntax", function(){
		var s = Selectize.init({
			dictionary: {
				addThis: "Ajouter %{elem}"
			}
		})

		expect(s.i18n('addThis', {elem: 'ceci'}).to.equal("Ajouter ceci")
	})

	describe("delegate to i18n library", function(){
		var I18n = {
			t: function(key, params) {
				return "Ajouter " + params.elem;
			}
		}

		var s = Selectize.init({
			i18n: I18n.t
		});

		expect(s.i18n('addThis', {elem: 'ceci'})).to.equal("Ajouter ceci")
	})

	describe("default i18n library", function(){
		var I18n = {
			t: function(key, params) {
				return "Ajouter " + params.elem;
			}
		}

		var s = Selectize.init({
			// i18n: default
		});

		expect(s.i18n('addThis', {elem: 'ceci'})).to.equal("Ajouter ceci")
	})
})
