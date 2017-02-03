function hideEmpty() {

  $('#symbols-container .symbols-empty').hide();
}

function showEmpty() {

  $('#symbols-container .symbols-empty').show();
}

function showEmptyIfNeed() {

  if ($('#symbols-container .row-symbol').length > 0) {

    hideEmpty();
  } else {

    showEmpty();
  }
}

function symbolAlreadyAdded(symbolId) {

  if ($('#symbols-container .row-symbol[data-symbol-id="' + symbolId +  '"]').length > 0) {
    return true;
  }

  return false;
}

$(document).ready(function() {

  $('body').delegate('.delete-symbol', 'click', function() {

    $(this).parents('tr').remove();

    showEmptyIfNeed();
  });

  $('#add-symbol').click(function() {

    hideEmpty();

    var name = $('#symbol').data('name');

    var selectedSymbolId = $('#symbol').val();

    if (symbolAlreadyAdded(selectedSymbolId)) {
      return true;
    }

    var symbolName = $('#symbol option:selected').text();

    $('#symbols-container tbody').append('<tr class="row-symbol" data-symbol-id="' + selectedSymbolId + '"><td class="cell-symbol-name">' + symbolName + '</td><td><button type="button" class="delete-symbol btn btn-danger"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span> Удалить</button><input name="' + name + '" type=hidden value="' + selectedSymbolId + '" /></td></tr>');
  });
});