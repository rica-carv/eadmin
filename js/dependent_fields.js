(function ($) {

    /**
     * Obtém valor real do pref
     */
    function getPrefValue(name) {

        var $el = $('[name="' + name + '"]');
        if (!$el.length) return null;

        // prioridade: hidden (padrão e107)
        var $hidden = $el.filter('input[type="hidden"]');
        if ($hidden.length) return String($hidden.val());

        // fallback checkbox normal
        if ($el.is(':checkbox')) {
            return $el.prop('checked') ? '1' : '0';
        }

        return String($el.val());
    }

    /**
     * Avalia regra simples (name:value)
     */
    function evaluateSingleRule(rule) {

        var parts = rule.split(':');
        if (parts.length !== 2) return false;

        var name  = parts[0].trim();
        var value = parts[1].trim();

        return getPrefValue(name) === value;
    }

    /**
     * Avalia regra completa com:
     * AND  -> a:1,b:0
     * OR   -> a:1|b:1
     */
    function evaluateRule(rule) {

        if (!rule) return true;

        // OR
        if (rule.indexOf('|') !== -1) {
            return rule.split('|').some(function (r) {
                return evaluateRule(r.trim());
            });
        }

        // AND
        if (rule.indexOf(',') !== -1) {
            return rule.split(',').every(function (r) {
                return evaluateRule(r.trim());
            });
        }

        return evaluateSingleRule(rule.trim());
    }

    /**
     * Atualiza todos os dependentes
     */
    function updateAllDependencies() {

        $('[data-depends]').each(function () {

            var $field = $(this);
            var rule   = $field.data('depends');
            var mode   = $field.data('depends-mode') || 'disable';

            var valid  = evaluateRule(rule);
var $row = $field.closest('tr');
//var isTab = $field.closest('.nav, .nav-tabs').length;

if (mode === 'hide') {

/*
    if (isTab) {
        $field.closest('li').toggle(valid);
    } else {
*/
        $row.toggle(valid);
//    }

} else {

/*
    if (isTab) {

        if (!valid) {
            $field.addClass('e-dep-disabled')
                  .attr('aria-disabled', 'true');
        } else {
            $field.removeClass('e-dep-disabled')
                  .removeAttr('aria-disabled');
        }

    } else {
*/
        $field.prop('disabled', !valid);

        if ($field.is('select') && typeof $field.select2 === 'function') {
            $field.trigger('change.select2');
        }

        if (!valid) {
            $row.addClass('e-dep-disabled').css({
                'opacity': '0.5',
                'pointer-events': 'none',
                'filter': 'grayscale(1)'
            });
        } else {
            $row.removeClass('e-dep-disabled').removeAttr('style');
        }
//    }
}

        });
    }

function applyTabDependencies() {

    var cfgs =
        (typeof e107 !== 'undefined'
        && e107.settings
        && e107.settings.adminTabDependencies)
            ? e107.settings.adminTabDependencies
            : null;

    if (!cfgs) return;

    $('.nav-tabs a.nav-link').each(function () {

        var $tab = $(this);
        var href = $tab.attr('href') || '';

        var match = href.match(/tab-(\d+)/);
        if (!match) return;

        var index = match[1];
        if (!cfgs[index]) return;

        var cfg   = cfgs[index];
        var valid = evaluateRule(cfg.depends);
        var $li   = $tab.closest('li');

        if (cfg.mode === 'hide') {
            $li.toggle(valid);
        } else {
            if (!valid) {
                $tab.addClass('e-dep-disabled')
                    .attr('aria-disabled', 'true');
            } else {
                $tab.removeClass('e-dep-disabled')
                    .removeAttr('aria-disabled');
            }
        }

        if (!valid && $tab.hasClass('active')) {
            $('.nav-tabs a.nav-link')
                .not('.e-dep-disabled')
                .filter(':visible')
                .first()
                .trigger('click');
        }
    });
}

    /**
     * INIT
     */
    $(document).ready(function () {

        updateAllDependencies();
        applyTabDependencies();

        // bootstrap-switch (robusto para e107)
        $(document).on(
            'switchChange.bootstrapSwitch change',
            '.bootstrap-switch input[type="checkbox"]',
            function () {
                updateAllDependencies();
                applyTabDependencies();
            }
        );

        // outros inputs
        $(document).on(
            'change keyup',
            'input, select, textarea',
            function () {
                updateAllDependencies();
                applyTabDependencies();
            }
        );
    });

})(jQuery);
