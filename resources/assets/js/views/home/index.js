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

})
