$(document).ready(function(){

  $('#loadCountriesWithCompetitions').click(function(){
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
            $('#countries').after('<div class="loader"></div>');
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

    $('#addFootballMatches').click(function(e){

        let lastMatch = $('.form-group.match-input').last().clone();
        let lastName = lastMatch.find('input').attr('name');

        let id = lastName.match(/\[(.*)\]/i)[1];
        let newId = parseInt(id)+1;


        lastMatch.find('label').attr('for', `match-${newId}`);
        lastMatch.find('label').attr('id', `labelMatch-${newId}`);

        lastMatch.find('input').attr('name', `match[${newId}]`);
        lastMatch.find('input').attr('id', `match-${newId}`);
        lastMatch.find('input').val("");
        let newMatch = lastMatch;

        newMatch.insertAfter('.form-group.match-input:last');
    })

    $('#calculateValueBets').click(function(e){
      e.preventDefault();
      let form  = $('#valueBetCalculatorContainer').find('form');
      let odds = $('#odds').val();
      let probability = $('#probability').val().replace('%', '');
      if( !form.hasClass('hidden')) {
        form.addClass('hidden');
        let betAmount = 10;
        let wonBetAmount = (betAmount * odds) - betAmount;
        let lostBetAmount = betAmount;
        let winChance = (probability/100).toFixed(2);
        let lossChance = ((100 - probability)/100).toFixed(2);

        let valueBetResult = ((wonBetAmount * winChance) - (lostBetAmount * lossChance)).toFixed(2)

        let valueBetClass = 'alert alert-success';
        if( valueBetResult < 0) {
          valueBetClass = 'alert alert-danger';
        }

        $('#valueBetCalculatorContainer .panel-heading').text('Result');
        $('#calculateValueBets').before(`<p style="width: 15%; text-align: center" class="value-bet-result ${valueBetClass}">Value bet:  ${valueBetResult}</p>`);
      } else {
        form.removeClass('hidden');
        $('#odds').val('');
        $('#probability').val('');
        $('#valueBetCalculatorContainer .panel-body').find('.value-bet-result').hide();
        $('#valueBetCalculatorContainer .panel-heading').text('Calculate Value Bet');
      }

    })

    $('.progress-bar').click(function(e) {

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

})
