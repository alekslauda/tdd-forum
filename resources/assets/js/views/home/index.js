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
      if( !form.hasClass('hidden')) {
        form.addClass('hidden');
        $('#valueBetCalculatorContainer .panel-heading').text('Value bet');
      } else {
        form.removeClass('hidden');
        $('#valueBetCalculatorContainer .panel-heading').text('Calculate Value Bet');
      }

    })

})
