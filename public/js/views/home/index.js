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

  $('#loadCountriesWithCompetitions').click(function (e) {

    $('#loadCountriesWithCompetitions').off();
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
        if (!$('.loader').length) {
          $('#countries').after('<div class="loader"></div>');
        }
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

  $('#calculateValueBets').click(function (e) {
    e.preventDefault();
    var form = $('#valueBetCalculatorContainer').find('form');
    var odds = $('#odds').val();
    var probability = $('#probability').val().replace('%', '');
    if (!form.hasClass('hidden')) {
      form.addClass('hidden');
      /**
       * START CALCULATING VALUE BET
       */
      var betAmount = 10;
      var wonBetAmount = betAmount * odds - betAmount;
      var lostBetAmount = betAmount;
      var winChance = (probability / 100).toFixed(2);
      var lossChance = ((100 - probability) / 100).toFixed(2);

      var valueBetResult = (wonBetAmount * winChance - lostBetAmount * lossChance).toFixed(2);

      /**
       * END
       */

      /**
       * START CALCULATING KELLY STRATEGY
       */
      var B = odds - 1;
      var P = probability / 100;
      var Q = 1 - P;
      var kellyStrategy = (B * P - Q) / B;
      /**
       * END
       */

      var valueBetClass = 'alert alert-danger';
      var message = '\n              <ul class="list-unstyled value-bet-result ' + valueBetClass + '">\n                  <li>There is no value here.</li>\n              </ul>';

      if (valueBetResult > 0) {
        valueBetClass = 'alert alert-success';
        message = '<ul class="list-unstyled value-bet-result ' + valueBetClass + '">\n              <li>Value bet:  ' + valueBetResult + '</li>\n              <ul class="list-unstyled">\n                <li>Applying <u>Kelly Critteria</u> bet:  <strong>' + Math.round(kellyStrategy * 100) + '%</strong> of your bank<br/><br/></li>\n                <li class="alert alert-info">Play safe end go for: <strong>' + Math.round(kellyStrategy.toFixed(2) * 100 / 2) + '%</strong> of your bank</li>\n              </ul>\n          </ul>';
      }

      $('#valueBetCalculatorContainer .panel-heading').text('Result');
      $('#calculateValueBets').before('' + message);
    } else {
      form.removeClass('hidden');
      $('#odds').val('');
      $('#probability').val('');
      $('#valueBetCalculatorContainer .panel-body').find('.value-bet-result').hide();
      $('#valueBetCalculatorContainer .panel-heading').text('Calculate Value Bet');
    }
  });

  $('.progress-bar').click(function (e) {

    var probability = $(e.target).text().trim();
    if (!$('#valueBetCalculatorContainer').find('form').hasClass('hidden')) {
      $('#probability').val(probability);
      $('#odds').focus();
      $([document.documentElement, document.body]).animate({
        scrollTop: $('#odds').offset().top
      }, 2000);
    } else {
      $('#valueBetCalculatorContainer').find('form').removeClass('hidden');
      $('#valueBetCalculatorContainer .panel-body').find('.value-bet-result').hide();
      $('#odds').val('');
      $('#odds').focus();
      $('#probability').val(probability);
      $('#valueBetCalculatorContainer .panel-heading').text('Calculate Value Bet');
    }
  });

  $(window).scroll(function () {
    if ($(this).scrollTop() > 50) {
      $('#back-to-top').fadeIn();
    } else {
      $('#back-to-top').fadeOut();
    }
  });
  // scroll body to 0px on click
  $('#back-to-top').click(function () {
    $('#back-to-top').tooltip('hide');
    $('body,html').animate({
      scrollTop: 0
    }, 800);
    return false;
  });

  $('#back-to-top').tooltip('show');

  $(window).scroll(function () {
    if ($(this).scrollTop() > 50) {
      $('#back-to-down').fadeIn();
    } else {
      $('#back-to-down').fadeOut();
    }
  });
  // scroll body to 0px on click
  $('#back-to-down').click(function () {
    $('#back-to-down').tooltip('hide');
    $('body,html').animate({
      scrollTop: $(document).height()
    }, 800);
    return false;
  });

  $('#back-to-down').tooltip('show');
});

/***/ })

/******/ });