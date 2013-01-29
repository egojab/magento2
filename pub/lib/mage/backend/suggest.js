/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    mage
 * @package     mage
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint jquery:true browser:true*/

(function($) {
    'use strict';
    /**
     * Implement base functionality
     */
    $.widget('mage.suggest', {
        options: {
            template: '',
            minLength: 1,
            /**
             * @type {(string|Array)}
             */
            source: null,
            delay: 500,
            events: {},
            appendMethod: 'after',
            control: 'menu',
            controls: {
                menu: {
                    selector: ':ui-menu',
                    eventsMap: {
                        focus: 'menufocus',
                        blur: 'menublur',
                        select: 'menuselect'
                    }
                }
            },
            wrapperAttributes: {
                'class': 'mage-suggest'
            },
            attributes: {
                'class': 'mage-suggest-dropdown'
            }
        },

        /**
         * Component's constructor
         * @private
         */
        _create: function() {
            this._setTemplate();
            this._term = '';
            this._selectedItem = {value: '', label: ''};
            this.dropdown = $('<div/>', this.options.attributes).hide();
            this.element
                .wrap($('<div><div class="mage-suggest-inner"></div></div>')
                .prop(this.options.wrapperAttributes))
                [this.options.appendMethod](this.dropdown)
                .attr('autocomplete', 'off');
            this.hiddenInput = $('<input/>', {
                type: 'hidden',
                name: this.element.attr('name')
            }).insertBefore(this.element);
            this.element.removeAttr('name');
            this._control = this.options.controls[this.options.control] || {};
            this._bind();
        },

        /**
         * Component's destructor
         * @private
         */
        _destroy: function() {
            this.element.removeAttr('autocomplete')
                .unwrap()
                .attr('name', this.hiddenInput.attr('name'));
            this.dropdown.remove();
            this.hiddenInput.remove();
            this._off(this.element, 'keydown keyup blur');
        },

        /**
         * Return actual value of an "input"-element
         * @return {string}
         * @private
         */
        _value: function() {
            return $.trim(this.element[this.element.is(':input') ? 'val' : 'text']());
        },

        /**
         * Pass original event to a control component for handling it as it's own event
         * @param {Object} event
         * @private
         */
        _proxyEvents: function(event) {
            this.dropdown.find(this._control.selector).triggerHandler(event);
        },

        /**
         * Bind handlers on specific events
         * @private
         */
        _bind: function() {
            this._on($.extend({
                keydown: function(event) {
                    var keyCode = $.ui.keyCode;
                    switch (event.keyCode) {
                        case keyCode.HOME:
                        case keyCode.END:
                        case keyCode.PAGE_UP:
                        case keyCode.PAGE_DOWN:
                        case keyCode.UP:
                        case keyCode.DOWN:
                        case keyCode.LEFT:
                        case keyCode.RIGHT:
                            if (!event.shiftKey) {
                                this._proxyEvents(event);
                            }
                            break;
                        case keyCode.TAB:
                            if (this.isDropdownShown()) {
                                this._selectItem();
                                event.preventDefault();
                            }
                            break;
                        case keyCode.ENTER:
                        case keyCode.NUMPAD_ENTER:
                            if (this.isDropdownShown()) {
                                this._proxyEvents(event);
                                event.preventDefault();
                            }
                            break;
                        case keyCode.ESCAPE:
                            this._hideDropdown();
                            break;
                    }
                },
                keyup: function(event) {
                    var keyCode = $.ui.keyCode;
                    switch (event.keyCode) {
                        case keyCode.HOME:
                        case keyCode.END:
                        case keyCode.PAGE_UP:
                        case keyCode.PAGE_DOWN:
                        case keyCode.ESCAPE:
                        case keyCode.UP:
                        case keyCode.DOWN:
                        case keyCode.LEFT:
                        case keyCode.RIGHT:
                            break;
                        case keyCode.ENTER:
                        case keyCode.NUMPAD_ENTER:
                            if (this.isDropdownShown()) {
                                event.preventDefault();
                            }
                            break;
                        default:
                            this.search();
                    }
                },
                blur: this._hideDropdown,
                cut: this.search,
                paste: this.search,
                input: this.search
            }, this.options.events));

            this._bindDropdown();
        },

        /**
         * Bind handlers for dropdown element on specific events
         * @private
         */
        _bindDropdown: function() {
            var events = {
                click: this._selectItem,
                mousedown: function(e) {
                    e.preventDefault();
                }
            };
            events[this._control.eventsMap.select] = this._selectItem;
            events[this._control.eventsMap.focus] = function(e, ui) {
                this.element.val(ui.item.text());
            };
            events[this._control.eventsMap.blur] = function() {
                this.element.val(this._term);
            };
            this._on(this.dropdown, events);
        },

        /**
         * Save selected item and hide dropdown
         * @private
         */
        _selectItem: function() {
            var term = this._value();
            if (this.isDropdownShown() && term) {
                /**
                 * @type {(Object|null)} - label+value object of selected item
                 * @private
                 */
                this._selectedItem = $.grep(this._items, $.proxy(function(v) {
                    return v.label === term;
                }, this))[0] || {value: '', label: ''};
                if (this._selectedItem.value) {
                    this._term = this._selectedItem.label;
                    this.hiddenInput.val(this._selectedItem.value);
                    this._hideDropdown();
                }
            }
        },

        /**
         * Check if dropdown is shown
         * @return {boolean}
         */
        isDropdownShown: function() {
            return this.dropdown.is(':visible');
        },

        /**
         * Open dropdown
         * @private
         */
        _showDropdown: function() {
            if (!this.isDropdownShown()) {
                this.dropdown.show();
            }
        },

        /**
         * Close and clear dropdown content
         * @private
         */
        _hideDropdown: function() {
            this.element.val(this._selectedItem.label);
            this.dropdown.hide().empty();
        },

        /**
         * Acquire content template
         * @private
         */
        _setTemplate: function() {
            this.template = $(this.options.template).length ?
                $(this.options.template).template() :
                $.template('suggestTemplate', this.options.template);
        },

        /**
         * Execute search process
         * @public
         */
        search: function() {
            var term = this._value();
            if (this._term !== term) {
                this._term = term;
                if (term) {
                    this._search(term);
                } else {
                    this._selectedItem = {value: '', label: ''};
                    this.hiddenInput.val(this._selectedItem.value);
                }
            }
        },

        /**
         * Actual search method, can be overridden in descendants
         * @param {string} term - search phrase
         * @param {Object} context - search context
         * @private
         */
        _search: function(term, context) {
            var renderer = $.proxy(function(items) {
                return this._renderDropdown(items, context || {});
            }, this);
            this.element.addClass('ui-autocomplete-loading');
            if (this.options.delay) {
                clearTimeout(this._searchTimeout);
                this._searchTimeout = this._delay(function() {
                    this._source(term, renderer);
                }, this.options.delay);
            } else {
                this._source(term, renderer);
            }
        },

        /**
         * Extend basic context with additional data (search results, search term)
         * @param {Object} context
         * @return {Object}
         * @private
         */
        _prepareDropdownContext: function(context) {
            return $.extend(context, {
                items: this._items,
                term: this._term
            });
        },

        /**
         * Render content of suggest's dropdown
         * @param {Array} items - list of label+value objects
         * @param {Object} context - template's context
         * @private
         */
        _renderDropdown: function(items, context) {
            this._items = items;
            $.tmpl(this.template, this._prepareDropdownContext(context))
                .appendTo(this.dropdown.empty());
            this.dropdown.trigger('contentUpdated');
            this._showDropdown();
        },

        /**
         * Implement search process via spesific source
         * @param {string} term - search phrase
         * @param {Function} renderer - search results handler, display search result
         * @private
         */
        _source: function(term, renderer) {
            if ($.isArray(this.options.source)) {
                renderer(this.filter(this.options.source, term));

            } else if ($.type(this.options.source) === 'string') {
                if (this._xhr) {
                    this._xhr.abort();
                }
                this._xhr = $.ajax($.extend({
                    url: this.options.source,
                    type: 'POST',
                    dataType: 'json',
                    data: {q: term},
                    success: renderer,
                    showLoader: true
                }, this.options.ajaxOptions || {}));
            }
        },

        /**
         * Perform filtering in advance loaded items and returns search result
         * @param {Array} items - all available items
         * @param {string} term - search phrase
         * @return {Object}
         */
        filter: function(items, term) {
            var matcher = new RegExp(term, 'i');
            return $.grep(items, function(value) {
                return matcher.test(value.label || value.value || value);
            });
        }
    });

    /**
     * Implements height prediction functionality to dropdown item
     */
    $.widget('mage.suggest', $.mage.suggest, {
        /**
         * Extension specific options
         */
        options: {
            bottomMargin: 35
        },

        /**
         * @override
         * @private
         */
        _renderDropdown: function() {
            this._superApply(arguments);
            this._recalculateDropdownHeight();
        },

        /**
         * Recalculates height of dropdown and cut it if needed
         * @private
         */
        _recalculateDropdownHeight: function() {
            var dropdown = this.dropdown.css('visibility', 'hidden'),
                fromTop = dropdown.offset().top,
                winHeight = $(window).height(),
                isOverflowApplied = (fromTop + dropdown.outerHeight()) > winHeight;

            dropdown
                .css('visibility', '')
                [isOverflowApplied ? 'addClass':'removeClass']('overflow-y')
                .height(isOverflowApplied ? winHeight - fromTop - this.options.bottomMargin : '');
        }
    });

    /**
     * Implement storing search history and display recent searches
     */
    $.widget('mage.suggest', $.mage.suggest, {
        options: {
            showRecent: true,
            storageKey: 'suggest',
            storageLimit: 10
        },

        /**
         * @override
         * @private
         */
        _create: function() {
            if (this.options.showRecent && window.localStorage) {
                var recentItems = JSON.parse(localStorage.getItem(this.options.storageKey));
                /**
                 * @type {Array} - list of recently searched items
                 * @private
                 */
                this._recentItems = $.isArray(recentItems) ? recentItems : [];
            }
            this._super();
        },

        /**
         * @override
         * @private
         */
        _bind: function() {
            this._super();
            this._on({
                focus: function() {
                    if (!this._value()) {
                        this._renderDropdown(this._recentItems);
                    }
                }
            });
        },

        /**
         * @override
         */
        search: function() {
            this._super();
            if (!this._term) {
                clearTimeout(this._searchTimeout);
                if (this._xhr) {
                    this._xhr.abort();
                }
                this._renderDropdown(this._recentItems);
            }
        },

        /**
         * @override
         * @private
         */
        _selectItem: function() {
            this._super();
            if (this._selectedItem.value) {
                this._addRecent(this._selectedItem);
            }
        },

        /**
         * Add selected item of search result into storage of recents
         * @param {Object} item - label+value object
         * @private
         */
        _addRecent: function(item) {
            this._recentItems = $.grep(this._recentItems, function(obj){
                return obj.value !== item.value;
            });
            this._recentItems.unshift(item);
            this._recentItems = this._recentItems.slice(0, this.options.storageLimit);
            localStorage.setItem(this.options.storageKey, JSON.stringify(this._recentItems));
        }
    });

    /**
     * Implement show all functionality
     */
    $.widget('mage.suggest', $.mage.suggest, {
        /**
         * @override
         * @private
         */
        _bind: function() {
            this._super();
            this._on(this.dropdown, {
                showAll: function() {
                    this._search('', {_allSown: true});
                }
            });
        },

        /**
         * @override
         * @private
         */
        _prepareDropdownContext: function() {
            var context = this._superApply(arguments);
            return $.extend(context, {allShown: function(){
                return !!context._allSown;
            }});
        }
    });
})(jQuery);
