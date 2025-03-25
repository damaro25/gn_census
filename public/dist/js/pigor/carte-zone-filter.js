$(document).ready(function () {
    // chargement des département à partir d'une région
    $('#region').change(function () {

        clearSelect();
        let regionId = $(this).val();

        $.ajax({
            url: "{{ path('censusmp_departements_region') }}",
            data: { id: regionId },
            method: 'GET',
            success: function (departements) {

                /*let qvhList = checkLocalitiesCreated(departements);
    
                if (qvhList.length > 0) {
                    $('#departement-div').show();
                    $('#departement').append("<option value=''></option>");
                }*/

                departements.map(dept => {
                    $('#departement').append("<option value='" + dept.code + "'>" + dept.nom + "</option>");
                });

            }, error: function (status, code) {

            }
        });
    });

    // chargement des communes à partir d'un département
    $('#departement').change(function () {

        $('#commune-div').hide();
        $('#commune').html("");

        $('#cacr-div').hide();
        $('#cacr').html("");

        $('#multiselect').html("");
        $('#multiselect_to').html("");

        let departementId = $(this).val();

        $.ajax({
            url: "{{ path('censusmp_communes_departement') }}",
            data: { id: departementId },
            method: 'GET',
            success: function (communes) {
                //console.log(communes);
                //let qvhList = checkLocalitiesCreated(communes);
                //console.log(qvhList);

                /*if (qvhList.length > 0) {
                    $('#commune-div').show();
                    $('#commune').append("<option value=''></option>");
                }*/

                communes.map(comm => {
                    $('#commune').append("<option value='" + comm.code + "'>" + comm.nom + "</option>");
                    $('#multiselect').append("<option value='" + comm.code + "'>" + comm.nom + "</option>");
                });

            }, error: function (status, code) {

            }
        });
    });

    // chargement des communes d'arrondissement ou villes à partir d'un département
    $('#commune').change(function () {

        $('#cacr-div').hide();
        $('#cacr').html("");

        $('#multiselect').html("");
        $('#multiselect_to').html("");

        let commVilleId = $(this).val();

        $.ajax({
            url: "{{ path('censusmp_communes_arr_comm_rurales_comvilles') }}",
            data: { id: commVilleId },
            method: 'GET',
            success: function (results) {

                /* let qvhList = checkLocalitiesCreated(results);
     
                 if (qvhList.length > 0) {
                     $('#cacr-div').show();
                     $('#cacr').append("<option value=''></option>");
                 }*/

                results.map(
                    res => {
                        $('#cacr').append("<option value='" + res.code + "'>" + res.nom + "</option>");
                        $('#multiselect').append("<option value='" + res.code + "'>" + res.nom + "</option>");
                    });

            }, error: function (status, code) {

            }
        });
    });

    // chargement des communes d'arrondissement ou villes à partir d'un département
    $('#cacr').change(function () {

        $('#multiselect').html("");
        $('#multiselect_to').html("");

        let commVilleId = $(this).val();

        $.ajax({
            url: "{{ path('censusmp_zones_cacr') }}",
            data: { id: commVilleId },
            method: 'GET',
            success: function (results) {

                // let qvhList = checkLocalitiesCreated(results);

                results.map(
                    res => {
                        $('#qvh').append("<option value='" + res.code + "'>" + res.nom + "</option>");
                    });

            }, error: function (status, code) {

            }
        });
    });

    // initialise ou vide les select lorsqu'on est sur un niveau plus haut sur le fitre
    function clearSelect() {
        $('#departement-div').hide();
        $('#departement').html("");

        $('#commune-div').hide("");
        $('#commune').html("");

        $('#cacr-div').hide("");
        $('#cacr').html("");

        $('#qvh').hide("");
        $('#qvh').html("");
    }
})