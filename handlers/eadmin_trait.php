<?php
// class2.php is the heart of e107, always include it first to give access to e107 constants and variables
// Isto é preciso por causa deste ficheiro ser acedido de dois paths diferentes....
if (!defined('e107_INIT'))
{
    require_once(e_BASE."class2.php");
}

if (!defined('e107_INIT')){exit;}

trait EAdmin_trait {

function new_record_button($url, $lan) // Só philcat_admin & e_dashboard....
{
	return "
            <div class='adminlist-footer text-center' style='margin-bottom:15px'>
                <a class='btn btn-primary e-btn-add' href='{$url}'>
                    <i class='fa fa-plus'></i> ".LAN_CREATE." {$lan}
                </a>
            </div>
        ";
}

public function divider()
{
    // devolve HTML que será inserido no formulário de prefs
    // personaliza a aparência como quiseres — aqui em bold e com apenas 1 linha estilizada
e107::css('inline', '
        /* estiliza a linha completa do divisor */
        tr:has(.form-divider) td {
            border-bottom: 2px solid #bbb !important; /* linha de separação */
        }

        /* remove borda superior da próxima linha */
        tr:has(.form-divider) + tr td {
            border-top: none !important;
        }
');

return "
<script>
document.addEventListener('DOMContentLoaded', function () {

  document.querySelectorAll('.form-divider').forEach(function (div) {

    // TR do divisor
    var tr = div.closest('tr');
    if (!tr) return;

    var table = tr.closest('table');
    if (!table) return;

    /* -------------------------------------------------
     * 1. Descobrir quantas colunas a table tem
     *    (igual ao que fizemos no user_friends)
     * ------------------------------------------------- */
    var maxCols = 0;

    table.querySelectorAll('tr').forEach(function (row) {
      var cols = 0;
      row.querySelectorAll('th, td').forEach(function (cell) {
        cols += cell.colSpan || 1;
      });
      maxCols = Math.max(maxCols, cols);
    });

    if (!maxCols) maxCols = 1;

    /* -------------------------------------------------
     * 2. Reduzir a linha a uma única TD com colspan total
     * ------------------------------------------------- */
    var firstTd = tr.querySelector('td, th');
    if (!firstTd) return;

    // remove as outras células
    tr.querySelectorAll('td, th').forEach(function (cell, idx) {
      if (idx !== 0) cell.remove();
    });

    firstTd.colSpan = maxCols;

    /* -------------------------------------------------
     * 3. Background coerente com o admin
     * ------------------------------------------------- */
    var panel =
      tr.closest('.admin-right-panel') ||
      document.querySelector('.admin-right-panel') ||
      document.body;

    function findNonTransparentBg(el) {
      var cur = el;
      while (cur && cur !== document.documentElement) {
        var bg = window.getComputedStyle(cur).backgroundColor;
        if (bg && bg !== 'transparent' && bg !== 'rgba(0, 0, 0, 0)') {
          return bg;
        }
        cur = cur.parentElement;
      }
      return window.getComputedStyle(document.body).backgroundColor || 'transparent';
    }

    var bgColor = findNonTransparentBg(panel);
    if (bgColor && bgColor !== 'transparent' && bgColor !== 'rgba(0, 0, 0, 0)') {
      tr.style.backgroundColor = bgColor;
    }

  });

});

</script>
    ";
}

    protected function fadenextfields()
{
e107::js('inline', "
document.addEventListener('DOMContentLoaded', function() {
  const mainSwitch = document.querySelector('#friend-sys--switch');
  if (!mainSwitch) return;

  const table = mainSwitch.closest('table');
  if (!table) return;

  const mainRow = mainSwitch.closest('tr');
  if (!mainRow) return;

  // todas as TRs após o mainRow
  const dependentRows = [];
  let next = mainRow.nextElementSibling;
  while (next) {
    if (next.tagName === 'TR') dependentRows.push(next);
    next = next.nextElementSibling;
  }

  function toggleRows(enabled) {
    dependentRows.forEach(row => {
      // alterna visualmente
      row.classList.toggle('disabled-row', !enabled);

      // desativa todos os campos dentro
      row.querySelectorAll('input, select, textarea, button').forEach(el => {
        if (typeof \$ !== 'undefined' && \$(el).data('bootstrapSwitch')) {
          try {
            \$(el).bootstrapSwitch('disabled', !enabled);
          } catch(e) {
            el.disabled = !enabled;
          }
        } else {
          el.disabled = !enabled;
        }
      });
    });
  }

  // estado inicial
  toggleRows(mainSwitch.checked);

  // listener de mudança
  if (typeof \$ !== 'undefined') {
    \$(mainSwitch).on('switchChange.bootstrapSwitch', function(event, state) {
      toggleRows(state);
    });
  } else {
    mainSwitch.addEventListener('change', function() {
      toggleRows(this.checked);
    });
  }
});
");

// CSS apenas para efeito visual (não bloqueia clique)
e107::css('inline', '
  .disabled-row {
    opacity: 0.5;
    transition: opacity 0.3s ease;
  }
');
}

/**
 * Verifica se um shortcode existe dentro de um template carregado.
 *
 * @param string $template  Conteúdo do template (string completa)
 * @param string $shortcode Nome do shortcode (sem underscores nem delimitadores)
 * @return bool True se o shortcode for encontrado
 */
public static function template_has_shortcode($template, $shortcode)
{
    if (empty($template) || empty($shortcode)) {
        return false;
    }

    // Se for array, converte para string única
    if (is_array($template)) {
        $template = implode("\n", $template);
    }

    return (bool) preg_match('/\{'.$shortcode.'(?:[=:].*?)?\}/i', $template);
}

}