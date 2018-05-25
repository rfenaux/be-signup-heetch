/* globals jQuery, _, socss, WebFont */

jQuery(function($) {
	var socss = window.socss;

	// The fonts select box controller
	socss.view.properties.controllers.font_select = socss.view.propertyController.extend({
		template: _.template('<div class="font-wrapper"><select></select></div>'),

		// Override init change events to ensure value is set correctly.
		initChangeEvents: function() {
			this.field.on('font_change', function(event, val, oldValue) {
				this.updateRule(val, oldValue);
			}.bind(this));
		},

		render: function() {

			this.$el.append($(this.template({})));
			this.field = this.$el.find('select');
			this.field.webfontselector({
				modules: this.args.modules,
			});
		},

		/**
		 * Set the current value
		 * @param socss.view.properties val
		 */
		setValue: function(val, options) {
			options = _.extend({silent: false}, options);

			this.field.webfontselector('update', val);

			if (!options.silent) {
				this.trigger('set_value', val);
			}
		},

		updateRule: function(newValue, oldValue) {
			var ruleVal = newValue.font ? "'" + newValue.font + "'" : '';
			ruleVal += newValue.category ? ", " + newValue.category : '';
			this.propertiesView.setRuleValue(
				this.args.property,
				ruleVal
			);

			var module;
			var importRule;
			var fontsInfo;
			if (newValue.webfont) {
				// Modify existing rule for font family, else create new one.
				module = this.args.modules[newValue.module];
				importRule = this.propertiesView.findImport(module.base_url);
				var newFamily = newValue.font.replace(new RegExp('\\s', 'g'), '+');

				if (importRule) {
					fontsInfo = this.importRuleToFontsInfo(importRule);

					if (fontsInfo.families.indexOf(newFamily) === -1) {
						fontsInfo.families.push(newFamily);
					}
					if (!(newValue.variant === '400' || newValue.variant === 'regular') &&
						fontsInfo.variants.indexOf(newValue.variant) === -1) {
						fontsInfo.variants.push(newValue.variant);
					}
					if (newValue.subset !== 'latin' && fontsInfo.subsets.indexOf(newValue.subset) === -1) {
						fontsInfo.subsets.push(newValue.subset);
					}
					fontsInfo.base_url = module.base_url;
					this.propertiesView.updateImport(module.base_url, this.fontsInfoToImportRule(fontsInfo));
				} else {
					fontsInfo = {
						base_url: module.base_url,
						families: [newFamily],
					};
					if (!(newValue.variant === '400' || newValue.variant === 'regular')) {
						fontsInfo.variants = [newValue.variant];
					}
					if (newValue.subset != 'latin') {
						fontsInfo.subsets = [newValue.subset];
					}
					this.propertiesView.addImport(this.fontsInfoToImportRule(fontsInfo));
				}
			}

			if (oldValue && oldValue.webfont) {
				module = this.args.modules[oldValue.module];
				importRule = this.propertiesView.findImport(module.base_url);
				fontsInfo = this.importRuleToFontsInfo(importRule);
				//get all unique font-family directives
				var usedFontFamilies = [];
				var rules = this.propertiesView.parsed.stylesheet.rules;
				for (var i = 0; i < rules.length; i++) {
					var rule = rules[i];
					if (!rule.declarations) {
						continue;
					}
					for (var j = 0; j < rule.declarations.length; j++) {
						var declaration = rule.declarations[j];
						if (declaration.property === 'font-family' && declaration.value) {
							var fam = declaration.value.match(/\'([^\']+)\'/)[1];
							if (usedFontFamilies.indexOf(fam) == -1) {
								usedFontFamilies.push(fam.replace(new RegExp('\\s', 'g'), '+'));
							}
						}
					}
				}

				var toRemove = [];
				for (var k = 0; k < fontsInfo.families.length; k++) {
					var fam = fontsInfo.families[k];
					if (usedFontFamilies.indexOf(fam) == -1) {
						toRemove.push(fam);
					}
				}

				// Old values not being used anymore. Remove it.
				for (var l = 0; l < toRemove.length; l++) {
					fontsInfo.families.splice(fontsInfo.families.indexOf(toRemove[l]), 1);
				}
				fontsInfo.base_url = module.base_url;
				if (fontsInfo.families.length > 0) {
					this.propertiesView.updateImport(module.base_url, this.fontsInfoToImportRule(fontsInfo));
				} else {
					this.propertiesView.removeImport(module.base_url);
				}

				// TODO: See if there's a way to remove subsets.
				// The above doesn't as it's difficult to figure out which subsets to remove without storing that information.
			}
		},

		importRuleToFontsInfo: function(importRule) {
			var fontsInfo = {
				families: [],
				variants: [],
				subsets: [],
			};

			var regex = /[\?|\&]([^=]+)\=([^&\s\)\;]+)/g;
			var qParams;
			while ((qParams = regex.exec(importRule.import)) !== null) {
				if (qParams[1] === 'family') {
					var s = qParams[2].split(':');
					fontsInfo.families = s[0].split('|');
					if (s.length > 1) {
						fontsInfo.variants = s[1].split(',');
					}
				} else if (qParams[1] === 'subset') {
					fontsInfo.subsets = qParams[2].split(',');
				}
			}
			return fontsInfo;
		},

		fontsInfoToImportRule: function(fontsInfo) {
			var newImportRule = {
				type: 'import'
			};

			var importStatement = 'url(' + fontsInfo.base_url + '?family=' + fontsInfo.families.join('|');
			if (!_.isEmpty(fontsInfo.variants)) {
				importStatement += ':' + fontsInfo.variants.join(',');
			}
			if (!_.isEmpty(fontsInfo.subsets)) {
				importStatement += '&subset=' + fontsInfo.subsets.join(',');
			}
			importStatement += ')';
			newImportRule.import = importStatement;
			return newImportRule;
		},

	});

	// Re-render editor after adding the font select control.
	var editor = window.socss.mainEditor;
	editor.visualProperties.render();
});
