/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 43);
/******/ })
/************************************************************************/
/******/ ({

/***/ 43:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(44);


/***/ }),

/***/ 44:
/***/ (function(module, exports) {

$(document).ready(function () {

  $('#loadCountriesWithCompetitions').click(function () {
    $.ajax({
      url: '/competition-countries',
      beforeSend: function beforeSend() {
        $('#loadCountriesWithCompetitions').after('<div class="loader"></div>');
      },
      success: function success(data) {
        $('.loader').remove();
        $('#loadCountriesWithCompetitions').hide();
        var competitionCountries = '<select class="form-control" id="countries">';

        var option = '';
        $.each(data, function (comp, href) {
          option += '<option value="' + href + '">' + comp + '</option>';
        });

        competitionCountries += option + '</select>';

        $('#loadCountriesWithCompetitions').parent().after('<div class="form-group">\n                <label for="countries">Choose a Country</label>\n                ' + competitionCountries + '\n            </div>');
      }
    });
  });

  $(document).on('change', '#countries', function () {
    var optionSelected = $(this).find("option:selected");
    var valueSelected = optionSelected.val();

    $.ajax({
      url: '/competition-build',
      data: { parentLink: valueSelected },
      beforeSend: function beforeSend() {
        $('#countries').after('<div class="loader"></div>');
        if ($('#competitions').length) {
          $('#competitions').attr('disabled', 'disabled');
        }
      },
      success: function success(data) {
        console.log(data);
        $('.loader').remove();
        if ($('.select-competitions').length) {
          $('.select-competitions').remove();
        }
        var competitionSelect = '<select name="competitions" class="form-control" id="competitions">';

        var option = '';
        $.each(data, function (comp, href) {
          option += '<option value="' + href + '">' + comp + '</option>';
        });

        competitionSelect += option + '</select>';

        $('#countries').parent().after('<div class="form-group select-competitions">\n                <label for="competitions">Choose a competition</label>\n                ' + competitionSelect + '\n            </div>');
      }
    });
  });

  $('#addFootballMatches').click(function (e) {

    var lastMatch = $('.form-group.match-input').last().clone();
    var lastName = lastMatch.find('input').attr('name');

    var id = lastName.match(/\[(.*)\]/i)[1];
    var newId = parseInt(id) + 1;

    lastMatch.find('label').attr('for', 'match-' + newId);
    lastMatch.find('label').attr('id', 'labelMatch-' + newId);

    lastMatch.find('input').attr('name', 'match[' + newId + ']');
    lastMatch.find('input').attr('id', 'match-' + newId);
    lastMatch.find('input').val("");
    var newMatch = lastMatch;

    newMatch.insertAfter('.form-group.match-input:last');
  });

  $('#calculateValueBets').click(function (e) {
    e.preventDefault();
    var form = $('#valueBetCalculatorContainer').find('form');
    var odds = $('#odds').val();
    var probability = $('#probability').val();
    if (!form.hasClass('hidden')) {
      form.addClass('hidden');
      var betAmount = 10;
      var wonBetAmount = betAmount * odds - betAmount;
      var lostBetAmount = betAmount;
      var winChance = (probability / 100).toFixed(2);
      var lossChance = ((100 - probability) / 100).toFixed(2);

      var valueBetResult = (wonBetAmount * winChance - lostBetAmount * lossChance).toFixed(2);

      var valueBetClass = 'alert alert-success';
      if (valueBetResult < 0) {
        valueBetClass = 'alert alert-danger';
      }

      $('#valueBetCalculatorContainer .panel-heading').html('<span class="' + valueBetClass + '">Value bet:  ' + valueBetResult + '</span>');
    } else {
      form.removeClass('hidden');
      $('#odds').val('');
      $('#probability').val('');
      $('#valueBetCalculatorContainer .panel-heading').text('Calculate Value Bet');
    }
  });

  $('.addProbability').click(function (e) {
    if (!$('#valueBetCalculatorContainer').find('form').hasClass('hidden')) {
      $('#probability').val($(e.target).text());
    }
  });
});

/***/ })

/******/ });