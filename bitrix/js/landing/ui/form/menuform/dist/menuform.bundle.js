this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,landing_loc,landing_env,landing_main,landing_ui_form_baseform,landing_ui_form_menuitemform,ui_draganddrop_draggable) {
	'use strict';

	var _templateObject, _templateObject2;

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	/**
	 * @memberOf BX.Landing.UI.Form
	 */

	var MenuForm = /*#__PURE__*/function (_BaseForm) {
	  babelHelpers.inherits(MenuForm, _BaseForm);

	  function MenuForm() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, MenuForm);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MenuForm).call(this, options));
	    main_core.Dom.addClass(_this.layout, 'landing-ui-form-menu');
	    _this.forms = new BX.Landing.UI.Collection.FormCollection();

	    if (main_core.Type.isArray(options.forms)) {
	      options.forms.forEach(function (form) {
	        _this.addForm(form);
	      });
	    }

	    _this.draggable = new ui_draganddrop_draggable.Draggable({
	      container: _this.getBody(),
	      context: parent.window,
	      draggable: '.landing-ui-form-menuitem',
	      dragElement: '.landing-ui-form-header-drag-button',
	      type: ui_draganddrop_draggable.Draggable.DROP_PREVIEW,
	      depth: {
	        margin: 20
	      },
	      offset: {
	        y: -65
	      }
	    });
	    _this.onMenuItemRemove = _this.onMenuItemRemove.bind(babelHelpers.assertThisInitialized(_this));
	    main_core.Dom.append(_this.getAddItemLayout(), _this.layout);
	    return _this;
	  }

	  babelHelpers.createClass(MenuForm, [{
	    key: "addForm",
	    value: function addForm(form) {
	      if (!this.forms.contains(form)) {
	        this.forms.add(form);
	        main_core.Dom.append(form.layout, this.body);
	        form.subscribe('remove', this.onMenuItemRemove.bind(this));

	        if (this.draggable) {
	          this.draggable.invalidateCache();
	        }
	      }
	    }
	  }, {
	    key: "onMenuItemRemove",
	    value: function onMenuItemRemove(event) {
	      var children = this.draggable.getChildren(event.data.form.layout);
	      children.forEach(function (element) {
	        main_core.Dom.remove(element);
	      });
	      this.forms.remove(event.data.form);
	      this.draggable.invalidateCache();
	    }
	  }, {
	    key: "serialize",
	    value: function serialize() {
	      var _this2 = this;

	      var draggableElements = this.draggable.getDraggableElements();

	      var getChildren = function getChildren(parent) {
	        var parentDepth = _this2.draggable.getElementDepth(parent);

	        var allChildren = _this2.draggable.getChildren(parent);

	        return allChildren.reduce(function (acc, current) {
	          var currentDepth = _this2.draggable.getElementDepth(current);

	          if (currentDepth === parentDepth + 1) {
	            var form = _this2.forms.getByLayout(current);

	            acc.push(_objectSpread(_objectSpread({}, form.serialize()), {}, {
	              children: getChildren(current)
	            }));
	          }

	          return acc;
	        }, []);
	      };

	      return draggableElements.reduce(function (acc, element) {
	        if (_this2.draggable.getElementDepth(element) === 0) {
	          var form = _this2.forms.getByLayout(element);

	          acc.push(_objectSpread(_objectSpread({}, form.serialize()), {}, {
	            children: getChildren(element)
	          }));
	        }

	        return acc;
	      }, []);
	    }
	  }, {
	    key: "onAddButtonClick",
	    value: function onAddButtonClick(event) {
	      event.preventDefault();
	      var pageType = landing_env.Env.getInstance().getType();
	      var content = {
	        text: landing_loc.Loc.getMessage('LANDING_NEW_PAGE_LABEL'),
	        target: '_blank',
	        href: ['KNOWLEDGE', 'GROUP'].includes(pageType) ? '#landing0' : ''
	      };
	      var allowedTypes = [BX.Landing.UI.Field.LinkUrl.TYPE_BLOCK, BX.Landing.UI.Field.LinkUrl.TYPE_PAGE, BX.Landing.UI.Field.LinkUrl.TYPE_CRM_FORM, BX.Landing.UI.Field.LinkUrl.TYPE_CRM_PHONE];

	      if (pageType === 'STORE') {
	        allowedTypes.push(BX.Landing.UI.Field.LinkUrl.TYPE_CATALOG);
	      }

	      var field = new BX.Landing.UI.Field.Link({
	        content: content,
	        options: {
	          siteId: landing_env.Env.getInstance().getSiteId(),
	          landingId: landing_main.Main.getInstance().id,
	          filter: {
	            '=TYPE': pageType
	          }
	        },
	        allowedTypes: allowedTypes
	      });
	      var form = new landing_ui_form_menuitemform.MenuItemForm({
	        fields: [field]
	      });
	      form.showForm();
	      this.addForm(form);
	      setTimeout(function () {
	        field.input.enableEdit();
	        var input = field.input.input;

	        var _input$childNodes = babelHelpers.slicedToArray(input.childNodes, 1),
	            textNode = _input$childNodes[0];

	        if (textNode) {
	          var range = document.createRange();
	          var sel = window.getSelection();
	          range.setStart(textNode, input.innerText.length);
	          range.collapse(true);
	          sel.removeAllRanges();
	          sel.addRange(range);
	        }
	      });
	    }
	  }, {
	    key: "getAddButton",
	    value: function getAddButton() {
	      var _this3 = this;

	      return this.cache.remember('addButton', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<button \n\t\t\t\t\tclass=\"ui-btn ui-btn-sm ui-btn-light-border ui-btn-icon-add ui-btn-round landing-ui-form-menu-add-button\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\t>\n\t\t\t\t\t", "\n\t\t\t\t</button>\n\t\t\t"])), _this3.onAddButtonClick.bind(_this3), landing_loc.Loc.getMessage('LANDING_ADD_MENU_ITEM'));
	      });
	    }
	  }, {
	    key: "getAddItemLayout",
	    value: function getAddItemLayout() {
	      var _this4 = this;

	      return this.cache.remember('addItemLayout', function () {
	        return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-form-menu-add\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this4.getAddButton());
	      });
	    }
	  }]);
	  return MenuForm;
	}(landing_ui_form_baseform.BaseForm);

	exports.MenuForm = MenuForm;

}((this.BX.Landing.UI.Form = this.BX.Landing.UI.Form || {}),BX,BX.Landing,BX.Landing,BX.Landing,BX.Landing.UI.Form,BX.Landing.UI.Form,BX.UI.DragAndDrop));
//# sourceMappingURL=menuform.bundle.js.map
