$(function () {
    $('#typeUserId').hide();
    $('#equipe_div').hide();
    $('#role_div').hide();

    $('#chefequipe_div').show();
    $('#supervisor_div').show();

    // L'affectation d'une équipe n'est possible que pour un AGENT_COLLECTE ou CHEF_EQUIPE à la création
    // Pour le superviseur, cette opération ne se fait qu'au moment de la création d'une équipe
    // dont il sera l'unique SUPERVISEUR
    $('#censusmp_agents_typeUtilisateur').change(function () {
        let roleId = parseInt($(this).val());
        if (roleId === 2) {
            $('#equipe_div').show();
            $('#chefequipe_div').hide();
            $('#supervisor_div').hide();
        } else {
            $('#equipe_div').hide();
            $('#chefequipe_div').show();
            $('#supervisor_div').show();
        }
    });

})
