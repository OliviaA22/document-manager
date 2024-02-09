const error = function() { throw new Error('ACL Tree View Plugin requires jQuery. Might be jquery missing or multiple declaration') }
typeof jQuery!="undefined" && !jQuery.isFunction( jQuery.fn.aclTreeView) ? jQuery(function($){
    "user strict";
    $.AclTreeView = function(element, options, dataset) {
        const settings = $.extend(true, {
            classes : {
                main : 'acl-tree-view',
                toggle : 'acl-tree-view-toggle',
                active : 'acl-tree-view-active',
                description : 'acl-tree-view-description'
            },
            icon : 'fas fa-sm fa-angle-right',
            data : 'acl-tree-view'
        }, $.AclTreeView.defaults);
        // DOM Element
        const __ul = $('<ul/>');
        const __li = $('<li/>');
        const __a = $('<a/>');
        const __i = $('<i/>');
        const __span = $('<span/>');

        // Instance
        $self = this;

        $.extend(settings, options);
        
        let $element = $(element).is('ul') && $(element).length>0 ? $(element) : null;

        if($element!=null && typeof dataset != 'undefined' && dataset!=null ) {
            this.attach(element, createNode(dataset, $element));
        }

        if($element==null && typeof dataset != 'undefined' && dataset!=null ) {
            $element = createNode(dataset);
            this.attach(element, $element);
        }

        if($element!=null && $element.length>0) {
            initializeView();
        }

        function initializeView() {
            $element.addClass(settings.classes.main);
            if('extraclass' in settings)
            $element.addClass(settings.extraclass);
            initializeToggle($element);
            initializeDescription($element);
            bindToggle($element);
            bindCallBack($element);
            if(settings.initCollapse || !settings.multy) {
                collapseAll($element, false, 0);
            }
        }

        function addNewElements(newdata) {
            if($element!=null && typeof newdata != 'undefined' && newdata!=null ) {
                const excepts = $element.find('ul');
                if(typeof dataset != 'undefined' && dataset!=null) {
                    dataset = dataset.concat($.isArray(newdata) ? newdata : [newdata]);
                }
                $self.attach(element, createNode(newdata, $element));
                initializeToggle($element);
                initializeDescription($element); 
                if(settings.initCollapse || !settings.multy) {
                    collapseAll($element, false, 0, excepts);
                }
            } else if($element==null && typeof newdata != 'undefined' && newdata!=null ) {
                dataset = $.isArray(newdata) ? newdata : [newdata];
                $element = createNode(dataset);
                $self.attach(element, $element);
                if($element!=null && $element.length>0) {
                    initializeView();
                }
            }
        }

        function updateElements(newdata) {
            const __old = dataset || []; 
            const __new = newdata || [];
            if(__new.length>0 && ( __old.length==0 || JSON.stringify(__old) != JSON.stringify(__new))) {
                dataset = $.isArray(__new) ? __new : [__new];
                $element = createNode(dataset);
                if(__old.length>0) {
                    collapse(element, settings.animationSpeed, function() {
                        element.html('');
                        element.css({'display':'none'});
                        $self.attach(element, $element);
                        if($element!=null && $element.length>0) {
                            initializeView();
                            element.slideDown(settings.animationSpeed);
                        }
                    }); 
                } else {
                    $self.attach(element, $element);
                    if($element!=null && $element.length>0) {
                        initializeView();
                        element.slideDown(settings.animationSpeed);
                    }
                }   
            }
        }

        function isElement($element, $elements) {
            for(let i = 0; i<$elements.length; i++) {
                if($element.is($elements[i])) return true;
            }
            return false;
        }

        function findRoots($element) {
            let roots = [];
            const $node = $element.closest('li').closest('ul');
            if($node.length>0) {
                roots.push($node);
            }

            if(!$node.is($self.joinClass(settings.classes.main, 'ul.'))) {
                roots = roots.concat(findRoots($node));
            }

            return roots;
        }

        function collapseAll($element, parent, speed, excepts) {
            if($element.is('ul')) {
                $element.find('>li').each(function(i, v) {
                    const $node = $(v);
                    const $level = $node.find('>ul');
                    if($level.length>0) {
                        collapseAll($level, true, speed, excepts);
                    }
                });
                
                if((parent || false) && !isElement($element, (excepts || []))) {
                    collapse($element.closest("li"), speed);
                }
            }
        }

        function collapse($element, speed, complete) {
            $element.find('>a').removeClass(settings.classes.active)
            $element.find('>ul').slideUp(typeof speed != 'undefined' && speed>=0 ? speed : settings.animationSpeed, isFunction(complete) ? complete : function() {});
        }

        function expand($element, speed, complete) {
            $element.find('>a').addClass(settings.classes.active)
            $element.find('>ul').slideDown(typeof speed != 'undefined' && speed>=0 ? speed : settings.animationSpeed, isFunction(complete) ? complete : function() {});
        }

        function toggle($element, speed) {
            const $toggle = $element.find('>a');
            if($toggle.length>0 && $toggle.hasClass(settings.classes.active)) {
                collapse($element, speed);
            } else {
                expand($element, speed);
            }
        }

        function bindCallBack($element) {
            if('callback' in settings && isFunction(settings.callback)) {
                $element.on('click', 'a:not('+$self.joinClass(settings.classes.toggle)+')', function(e) {
                    const $node = $(this);
                    const params = $node.data(settings.data) || {};
                    settings.callback(e, $node, params);
                });
            }
        }

        function bindToggle($element) {
            $element.on('click', $self.joinClass(settings.classes.toggle, 'a.'), function(e) {
                e.preventDefault();
                const $node = $(this).closest("li");
                if(!settings.multy) {
                    const current = $node.find('>ul');
                    let roots = findRoots(current);
                        roots = roots.concat([current]);
                    collapseAll($node.closest($self.joinClass(settings.classes.main, 'ul.')), false, null, roots);
                }
                toggle($node);
            });
        }

        function initializeToggle($element) {
            $element.find('>li').each(function(i, node) {
                const $node = $(node);
                const $level = $node.find('>ul');
                if(!$node.has('>ul').find('>a').hasClass(settings.classes.toggle)) {
                    $node.has('>ul').find('>a').addClass(settings.classes.toggle).addClass(
                        !settings.initCollapse ? settings.classes.active : ''
                    ).prepend(' ').prepend(
                        __i.clone().addClass(settings.icon)
                    );
                } 
                if($level.length>0) {
                    initializeToggle($level);
                }
            });
        }

        function initializeDescription($element) {
            $element.find('>li').each(function(i, node) {
                const $node = $(node);
                const $level = $node.find('>ul');
                if($level.length>0) {
                    $label = $node.find('>a').find('span');
                    if($label.length>1) 
                    if(!$node.find('>a').find('span:last-child').hasClass(settings.classes.description)) 
                    $node.find('>a').find('span:last-child').addClass(settings.classes.description);
                    initializeDescription($level);
                } else {
                    if(!$node.find('>a>span').hasClass(settings.classes.description))
                    $node.find('>a>span').addClass(settings.classes.description);
                }
            });
        }
        
        function createNode(dataset, $parent) {
            const parent = $parent || __ul.clone();
            if(dataset.length>0 || ($.isPlainObject(dataset) && Object.keys(dataset).length>0)){
                if($.isArray(dataset)) {
                    $.each(dataset, function(i, v) {
                        if('ul' in v)
                            renderUl(parent, v, createNode(v.ul))
                        else
                            renderLi(parent, v)
                    });
                } else if($.isPlainObject(dataset)){
                    if('ul' in dataset)
                        renderUl(parent, dataset, createNode(dataset.ul))
                    else
                        renderLi(parent, dataset)
                }
            }
            return parent;
        }

        function renderUl(parent, dataset, child) {
            parent.append(__li.clone().append(
                __a.clone().attr('href', 'javascript:void(0)').append(
                    __span.clone().append(
                        'icon' in dataset ? __i.clone().addClass(dataset.icon) : ''
                    ).append(
                        'icon' in dataset ? ' ' : ''
                    ).append(dataset.label)
                ).append(
                    'description' in dataset ? __span.clone().addClass(settings.classes.description).append(
                        'dRender' in settings && $.isFunction(settings.dRender) ? settings.dRender.call(dataset.description) : document.createTextNode(dataset.description)
                    ) : ''
                )
            ).append(child));
        }

        function renderLi(parent, dataset) {
            parent.append(__li.clone().append(
                __a.clone().attr('href', ('href' in dataset ? dataset.href : 'javascript:void(0)')).append(
                    'icon' in dataset ? __i.clone().addClass(dataset.icon) : ''
                ).append('icon' in dataset ? ' ' : '').append(dataset.label).append(
                    'description' in dataset ? __span.clone().addClass(settings.classes.description).append(
                        'dRender' in settings && $.isFunction(settings.dRender) ? settings.dRender.call(dataset.description) : document.createTextNode(dataset.description)
                    ) : ''
                ).data(settings.data, dataset)
            ));
        }

        function isFunction(fnc) {
            return fnc && {}.toString.call(fnc) === '[object Function]';
        }

        $self.add = function(newdata) {
            addNewElements(newdata);
        };

        $self.update = function(newdata) {
            updateElements(newdata);
        };
    };

    $.extend($.fn, {
        aclTreeView : function(options, dataset) {
            return new $.AclTreeView(this, options, dataset);
        }
    });

    $.extend($.AclTreeView, {
        defaults : {
            initCollapse : true,
            animationSpeed : 400,
            multy : false,
            callback : null, //function(event, $element, params) {}
        },
        setDefaults: function(options) {
            $.extend( $.AclTreeView.defaults, options );
        },
        isAclTreeView : function(element) {
            return $(element).is('div.acl-tree-view');
        },
        create : function(parent, dataset, options) {
            return new $.AclTreeView(parent, options, dataset);
        },
        prototype : {
            attach : function(parent, child) {
                parent.append(child);
            },
            joinClass : function (className, operator) {
                return (operator || '.')+className;
            },
        }
    });

}):error();