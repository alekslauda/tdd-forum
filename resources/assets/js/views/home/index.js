$(document).ready(function(){

  $('#loadCountriesWithCompetitions').click(function(e){

    $('#loadCountriesWithCompetitions').off();
      $.ajax({
        url: '/competition-countries',
        beforeSend: function() {
          $('#loadCountriesWithCompetitions').after('<div class="loader"></div>')
        },
        success: function( data ) {
          $('.loader').remove();
          $('#loadCountriesWithCompetitions').hide();
          let competitionCountries = '<select class="form-control" id="countries">';

          let option = '';
          $.each( data, function (comp, href) {
            option += `<option value="${href}">${comp}</option>`;
          })

          competitionCountries += `${option}</select>`;

          $('#loadCountriesWithCompetitions').parent().after(`<div class="form-group">
                <label for="countries">Choose a Country</label>
                ${competitionCountries}
            </div>`);
        }
      })
  })

  $(document).on('change', '#countries', function(){
    var optionSelected = $(this).find("option:selected");
    var valueSelected  = optionSelected.val();

      $.ajax({
          url: '/competition-build',
          data: { parentLink: valueSelected },
          beforeSend: function() {
            if ( !$('.loader').length) {
              $('#countries').after('<div class="loader"></div>');
            }
            if ($('#competitions').length) {
              $('#competitions').attr('disabled', 'disabled');
            }
          },
        success: function ( data ) {
            console.log(data);
          $('.loader').remove();
          if ($('.select-competitions').length) {
            $('.select-competitions').remove();
          }
          let competitionSelect = '<select name="competitions" class="form-control" id="competitions">';

          let option = '';
          $.each( data, function (comp, href) {
            option += `<option value="${href}">${comp}</option>`;
          })

          competitionSelect += `${option}</select>`;

          $('#countries').parent().after(`<div class="form-group select-competitions">
                <label for="competitions">Choose a competition</label>
                ${competitionSelect}
            </div>`);
        }
      });
  })

    $('#calculateValueBets').click(function(e){
      e.preventDefault();
      let form  = $('#valueBetCalculatorContainer').find('form');
      let odds = $('#odds').val();
      let bankroll =  $('#bankroll').val() ? $('#bankroll').val() : 100;
      let probability = $('#probability').val().replace('%', '');
      if( !form.hasClass('hidden')) {
        form.addClass('hidden');
        /**
         * START CALCULATING VALUE BET
         */
          let betAmount = 10;
          let wonBetAmount = (betAmount * odds) - betAmount;
          let lostBetAmount = betAmount;
          let winChance = (probability/100).toFixed(2);
          let lossChance = ((100 - probability)/100).toFixed(2);

          let valueBetResult = ((wonBetAmount * winChance) - (lostBetAmount * lossChance)).toFixed(2)

        /**
         * END
         */

        /**
         * START CALCULATING KELLY STRATEGY
         */
          let B = odds - 1;
          let P = probability/100;
          let Q = 1 - P;
          let overlay = ((P * odds) - 1) * 100;
          let fraction = 10;

          let fractionKelly = (bankroll * (fraction/100)) * ((overlay/100)/(B));
          let kellyStrategy = (B*P - Q) / B;
        /**
         * END
         */


        let valueBetClass = 'alert alert-danger';
        let message = `
              <ul class="list-unstyled value-bet-result ${valueBetClass}">
                  <li>There is no value here.</li>
              </ul>`;

        if( valueBetResult > 0) {
          valueBetClass = 'alert alert-success';
          message = `<ul class="list-unstyled value-bet-result ${valueBetClass}">
              <li>Value bet:  ${valueBetResult}</li>
              <ul class="list-unstyled">
                <li>Applying <u>Kelly Critteria</u> bet:  <strong>${Math.round(kellyStrategy*100)}%</strong> of your bank<br/><br/></li>
                <li class="alert alert-info">Play safe end go for: <strong>${Math.round(((kellyStrategy.toFixed(2))*100)/2)}%</strong> of your bank</li>
                <li class="alert alert-info">With <strong>Kelly Fraction</strong> and <strong>${bankroll}$</strong> bankroll: Bet <strong>${Math.round(fractionKelly)}</strong>$.</li>
              </ul>
          </ul>`;
        }


        $('#valueBetCalculatorContainer .panel-heading').text('Result');
        $('#calculateValueBets').before(`${message}`);
      } else {
        form.removeClass('hidden');
        $('#bankroll').val('');
        $('#odds').val('');
        $('#probability').val('');
        $('#valueBetCalculatorContainer .panel-body').find('.value-bet-result').hide();
        $('#valueBetCalculatorContainer .panel-heading').text('Calculate Value Bet');
      }

    })

    $('.percentage:not(.vincent)').click(function(e) {

        let probability = $(e.target).text().trim();
        if( !$('#valueBetCalculatorContainer').find('form').hasClass('hidden')) {
          $('#probability').val(probability)
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
    })

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

})
