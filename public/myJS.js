function afficherIndicateur(url_request) {
        $('#dashboard-block').html(
            '<div class="loader animation-start"><span class="circle delay-1 size-1"></span><span class="circle delay-2 size-2"></span><span class="circle delay-3 size-2"></span></div>'
        );

        $.ajax({
            url: url_request,
            dataType: "json",
            method: "GET",
            success: function (indicators) {
                $('#dashboard-block').html("");
                console.log('INDIC', indicators);
                indicators.map((oneDiv) => {
                    var tailleSm = oneDiv.taille_mobile;
                    if(tailleSm == null){
                        tailleSm = oneDiv.taille
                    }
                    if(oneDiv.type == 'blocks'){
                        $('#dashboard-block').append(`
                            <div class="col-md-${tailleSm} col-xl-${oneDiv.taille}" style="padding-left:2px; padding-right:2px">
                                <div class="card client-blocks" style="border-top-color:${oneDiv.couleur}">
                                    <div class="card-block" style="padding-top: 0.5rem; padding-bottom: 0.5rem">
                                        <h5>${oneDiv.libelle}</h5>
                                        <ul>
                                            <li style="color:${oneDiv.couleur}">
                                                <i class="${oneDiv.icone}"></i>
                                            </li>
                                            <li id="" class="text-right" style="color:${oneDiv.couleur}">
                                                ${oneDiv.valeur}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        `
                        );
                    }
                    else if(oneDiv.type == 'tables'){
                        $('#dashboard-block').append(`
                            <div class="col-md-${tailleSm} col-xl-${oneDiv.taille}" style="padding-left:2px; padding-right:2px">
                                <div class="card table-card" style="border-top:none; background-color:${oneDiv.couleur}; color:#fff">
                                    <div class="row-table" style="padding:0.35em">
                                        <div class="col-sm-3 card-block-big" style="text-align:center; background-color:${oneDiv.darker_couleur}">
                                            <i class="${oneDiv.icone}"></i>
                                        </div>
                                        <div class="col-sm-9">
                                            <h4>${oneDiv.valeur}</h4>
                                            <h6>${oneDiv.libelle}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `
                        );
                    }
                    else if(oneDiv.type == 'socials'){
                        $('#dashboard-block').append(`
                            <div class="col-md-${tailleSm} col-xl-${oneDiv.taille}" style="padding-left:2px; padding-right:2px">
                                <div class="card social-widget-card">
                                    <div class="card-block-big" style="background-color:${oneDiv.couleur}; padding:1.25em">
                                        <h3>${oneDiv.valeur}</h3>
                                        <span class="m-t-10">${oneDiv.libelle}</span>
                                        <i class="${oneDiv.icone}"></i>
                                    </div>
                                </div>
                            </div>
                        `
                        );
                    }
                    else if(oneDiv.type == 'carte'){
                        $('#dashboard-block').append(`
                            <div class="col-md-${tailleSm} col-xl-${oneDiv.taille}" style="padding-left:2px; padding-right:2px">
                                <div class="card">
                                    <div class="card-header">
                                        <span class="text-uppercase">${oneDiv.libelle}</span>
                                    </div>
                                    <div class="card-block">
                                        <div id="carte_${oneDiv.id}" style="display: block; height: 500px; width: auto;"></div>
                                    </div>
                                </div>
                            </div>
                        `
                        );
                        displayMap(oneDiv.id, oneDiv.nom_carte, oneDiv.donnees, oneDiv.libelle, oneDiv.valeur_min, oneDiv.valeur_max, oneDiv.couleur_min, oneDiv.couleur_max, oneDiv.couleur_hover, oneDiv.property, oneDiv.valeur_null, oneDiv.unite_mesure)
                    }
                    else if(oneDiv.type == 'horizontal diagram'){
                        $('#dashboard-block').append(`
                            <div class="col-md-${tailleSm} col-lg-${oneDiv.taille}" style="padding-left:2px; padding-right:2px">
                                <div class="card">
                                    <div class="card-header">
                                        <span class="text-uppercase">${oneDiv.libelle}</span>
                                    </div>
                                    <div id="" class="card-block">
                                        <canvas id="diagrm_${oneDiv.id}" style="display: block; height: 410px; width: 100%;"></canvas>
                                    </div>
                                </div>
                            </div>
                        `
                        );
                        displayDiagram(oneDiv.id, oneDiv.labels, oneDiv.datas, oneDiv.libelle, oneDiv.couleur)
                    }
                    else if(oneDiv.type == 'stacked diagram'){
                        $('#dashboard-block').append(`
                            <div class="col-md-${tailleSm} col-lg-${oneDiv.taille}" style="padding-left:2px; padding-right:2px">
                                <div class="card">
                                    <div class="card-header">
                                        <span class="text-uppercase">${oneDiv.libelle}</span>
                                    </div>
                                    <div id="" class="card-block">
                                        <canvas id="diagrm_${oneDiv.id}" style="display: block; height: 410px; width: 100%;"></canvas>
                                    </div>
                                </div>
                            </div>
                        `
                        );
                        displayStacked(oneDiv.id, oneDiv.labels, oneDiv.datasets, oneDiv.scales, oneDiv.libelle, oneDiv.couleur)
                    }
                    else if(oneDiv.type == 'vertical diagram'){
                        $('#dashboard-block').append(`
                            <div class="col-md-${tailleSm} col-lg-${oneDiv.taille}" style="padding-left:2px; padding-right:2px">
                                <div class="card">
                                    <div class="card-header">
                                        <span class="text-uppercase">${oneDiv.libelle}</span>
                                    </div>
                                    <div id="" class="card-block">
                                        <div id="diagrm_${oneDiv.id}" style="display: block; height: 410px; width: 100%;"></div>
                                    </div>
                                </div>
                            </div>
                        `
                        );
                        displayVertical(oneDiv.id, oneDiv.datas, oneDiv.libelle, oneDiv.couleur)
                    }
                    else if(oneDiv.type == 'linear'){
                        $('#dashboard-block').append(`
                            <div class="col-md-${tailleSm} col-xl-${oneDiv.taille}" style="padding-left:2px; padding-right:2px">
                                <div class="card">
                                    <div class="card-header">
                                        <span class="text-uppercase">${oneDiv.libelle}</span> 
                                    </div>
                                    <div class="card-block">
                                        <div id="diagrm_${oneDiv.id}"style="display: block; height: 350px; width: 100%;"></div>
                                    </div>
                                </div>
                            </div>
                        `
                        );
                        displayLinear(oneDiv.id, oneDiv.datas, oneDiv.couleur)
                    }
                    else if(oneDiv.type == 'pyramide'){
                        $('#dashboard-block').append(`
                            <div class="col-md-${tailleSm} col-lg-${oneDiv.taille}" style="padding-left:2px; padding-right:2px">
                                <div class="card">
                                    <div class="card-header">
                                        <span class="text-uppercase">${oneDiv.libelle}</span> 
                                    </div>
                                    <div id="" class="card-block">
                                        <canvas id="diagrm_${oneDiv.id}" style="display: block; height: 410px; width: 100%;"></canvas>
                                    </div>
                                </div>
                            </div>
                        `
                        );
                        displayPyramide(oneDiv.id, oneDiv.labels, oneDiv.datasets)
                    }
                    else if(oneDiv.type == 'pie'){
                        $('#dashboard-block').append(`
                            <div class="col-md-${tailleSm} col-lg-${oneDiv.taille}" style="padding-left:2px; padding-right:2px">
                                <div class="card">
                                    <div class="card-header">
                                        <span class="text-uppercase">${oneDiv.libelle}</span>
                                    </div>
                                    <div id="" class="card-block">
                                        <canvas id="diagrm_${oneDiv.id}" style="display: block; height: 410px; width: 100%;"></canvas>
                                    </div>
                                </div>
                            </div>
                        `
                        );
                        displayPie(oneDiv.id, oneDiv.labels, oneDiv.datas, oneDiv.couleurs, oneDiv.couleurs_hover)
                    }
                    else if(oneDiv.type == 'tableau'){
                        $('#dashboard-block').append(`
                            <div class="col-md-${tailleSm} col-xl-${oneDiv.taille}" style="padding-left:2px; padding-right:2px">
                                <div class="card">
                                    <div class="card-header">
                                        <span class="text-uppercase">${oneDiv.libelle}</span>
                                    </div>
                                    <div class="card-block table-border-style">
                                        <div class="table-responsive" style="height: 450px; ">
                                            <table id="dyn_table_${oneDiv.id}" class="table table-styling text-center">
                                                <thead>
                                                    <tr class="text-white text-center" style="background-color: ${oneDiv.couleur_header} ;">
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `
                        );
                        displayTableau(oneDiv.id, oneDiv.colonnes, oneDiv.datas)
                    }
                });

                if (indicators?.length == 0) {
                    $('#dashboard-block').html(`
                        <div class="col-sm-12">
                            <div class="auth-body">
                                <div clas="text-center">
                                    <h2>Bienvenu(e)!</h2>
                                    <h3>Vous aurez bientôt vos indicateurs</h3>
                                    <h5 class="text-info">Contactez l'administrateur pour plus d'information</h5>
                                </div>
                            </div>
                        </div>
                    `);;
                }

            }, error: function (status, text) {
                console.error('status', status);
            }
        });
}

function addSpaces(nStr)
{
    var remainder = nStr.length % 3;
    return (nStr.substr(0, remainder) + nStr.substr(remainder).replace(/(\d{3})/g, ' $1')).trim();
}


function displayMap(idCarte, json, donnee, title, valeur_min, valeur_max, couleur_min, couleur_max, couleur_hover, property, val_null, unite)
{   
    var base_url = $('#baseUrl').text();
    var myCartUrl = base_url+ '/cartes/' +json
    console.log('BASE_URL', base_url);
    (async () => {

        const topology = await fetch(
            myCartUrl
        ).then(response => response.json());

        // Prepare demo data. The data is joined to map using value of 'hc-key'
        // property by default. See API docs for 'joinBy' for more info on linking
        // data and map.
        console.log('CHART', idCarte, json, donnee, title, valeur_min, valeur_max, couleur_min, couleur_max, couleur_hover, property, val_null, unite);
        const data = donnee;
        // Create the chart
        Highcharts.mapChart('carte_'+idCarte, {
            chart: {
                map: topology
            },

            title: {
                text: ''
            },

            // subtitle: {
            //     text: 'Source map: <a href="http://code.highcharts.com/mapdata/countries/sn/sn-all.topo.json">National</a>'
            // },

            mapNavigation: {
                enabled: true,
                buttonOptions: {
                    verticalAlign: 'bottom'
                }
            },

            colorAxis: {
                min: valeur_min,
                max: valeur_max,
                // type: 'linear',
                minColor: couleur_min,
                maxColor: couleur_max
            },
            tooltip: {
                formatter: function () {

                    return '<b>' + this.point.name + '</b><br>' +
                            '<b>' + this.point.value + ' ' +unite+ '</b>';
                }
            },

            series: [{
                data: data,
                joinBy: property,
                name: '',
                states: {
                    hover: {
                        color: couleur_hover
                    }
                },
                dataLabels: {
                    enabled: true,
                    format: '{point.value} ' +unite,
                    nullFormat: val_null,
                },
            }]
        });

    })
    ();
}

function displayDiagram(idDiagram, labels, datas, title, couleur)
{

                var prog = {
                labels:  labels,
                datasets: [
                    {
                        label: "",
                        backgroundColor: couleur,
                        borderWidth: 1,
                        data: datas,
                        yAxisID: "bar-y-axis1",

                    },
                ],
                };

            var progbar = document.getElementById("diagrm_"+idDiagram).getContext('2d');
            var myBarChart = new Chart(progbar, {
                type: "horizontalBar",
                data: prog,
                options: {
                    responsive: true,
                    legend: {
                        position: 'top' // place legend on the right side of chart
                    },
                    title: {
                        display: true,
                        text: title
                    },

                    scales: {
                        yAxes: [{
                        stacked: true,
                        id: "bar-y-axis1",
                        barThickness: 10,
                        maxBarThickness: 20,
                        minBarLength: 8,
                        }],
                        xAxes: [{
                        stacked: false,
                        ticks: {
                            beginAtZero: true
                        },
                        }]

                    }
                },

            }); 
}

function displayStacked(idDiagram, labels, datasets, scales, title, couleur) {
        // attendu = response.attendu;
        // collecte = response.collecte;

        var prog = {
            labels: labels,
            datasets: datasets,
        };

        var progbar = document.getElementById("diagrm_"+idDiagram).getContext('2d');
        var myBarChart = new Chart(progbar, {
            type: "horizontalBar",
            data: prog,
            options: {
                responsive: true,
                legend: {
                    position: 'top' // place legend on the right side of chart
                },
                title: {
                    display: true,
                    text: title
                },

                scales: {
                    yAxes: scales,
                    xAxes: [{
                        stacked: false,
                        ticks: {
                            beginAtZero: true
                        },
                    }]

                }
            },

        });
}


function displayVertical(idDiagram, datas, title, couleur) {
    Highcharts.chart("diagrm_"+idDiagram, {
        chart: {
            type: 'column'
        },
        title: {
            align: 'center',
            text: ''
        },
        subtitle: {
            align: 'center',
        },
        accessibility: {
            announceNewData: {
                enabled: true
            }
        },
        xAxis: {
            type: 'category'
        },
        // yAxis: {
        //     title: {
        //         text: ordonnee // axe des ordonnées
        //     }

        // },
        legend: {
            enabled: false
        },
        plotOptions: {
            series: {
                borderWidth: 0,
                dataLabels: {
                    enabled: true,
                    format: '{point.y:.1f}'
                }
            }
        },

        tooltip: {
            headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
        },

        series: [
            {
                // name: abscisse, // text des Abscisses
                color: couleur,
                data: datas
            }
        ],
    });
}


function displayTableau(idTableau, colonnes, datas) {

    let col = [];
    $('#dyn_table_'+idTableau+' > thead > tr').html('');

    colonnes.map((th) => {
        // console.log('TH', th);
        $('#dyn_table_'+idTableau+' > thead > tr').append(`
            <th class="text-center">${th}</th>`
        );

        col.push(
            {"data": th},
        );
    });

    if ($.fn.dataTable.isDataTable('#dyn_table_'+idTableau)) {
        console.log('COLUMNS', col, datas);

        $('#dyn_table_'+idTableau).DataTable().destroy();

        table = $('#dyn_table_'+idTableau).dataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.11.3/i18n/fr_fr.json'
            },
            "aaData": datas,
            "columns": col,
            "retrieve": true
        });
    }
    else {
        console.log('COLUMNS', col, datas);

        table = $('#dyn_table_'+idTableau).dataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.11.3/i18n/fr_fr.json'
            },
            "aaData": datas,
            "columns": col,
            "retrieve": true
        });
    }
}

function displayLinear(idDiagram, datas, couleur) {

    var chart = AmCharts.makeChart("diagrm_"+idDiagram, {
        "type": "serial",
        "theme": "light",
        "marginRight": 40,
        "marginLeft": 40,
        "autoMarginOffset": 20,
        "mouseWheelZoomEnabled": true,
        "valueAxes": [{
            "id": "v1",
            "axisAlpha": 0,
            "position": "left",
            "ignoreAxisWidth": true
        }],
        "balloon": {
            "borderThickness": 1,
            "shadowAlpha": 0
        },
        "graphs": [{
            "id": "g1",
            "balloon": {
                "drop": true,
                "adjustBorderColor": false,
                "color": "#ffffff"
            },
            // "bullet": "round",
            "bulletBorderAlpha": 1,
            "bulletColor": "#FFFFFF",
            "bulletSize": 5,
            "hideBulletsCount": 50,
            "lineThickness": 2,
            "title": "red line",
            "useLineColorForBulletBorder": true,
            "valueField": "value",
            "balloonText": "<span style='font-size:18px;'>[[value]]</span>"
        }],
        "chartScrollbar": {
            "graph": "g1",
            "oppositeAxis": false,
            "offset": 30,
            "scrollbarHeight": 80,
            "backgroundAlpha": 0,
            "selectedBackgroundAlpha": 0.1,
            "selectedBackgroundColor": "#888888",
            "graphFillAlpha": 0,
            "graphLineAlpha": 0.5,
            "selectedGraphFillAlpha": 0,
            "selectedGraphLineAlpha": 1,
            "autoGridCount": true,
            "color": "#AAAAAA"
        },
        "chartCursor": {
            "pan": true,
            "valueLineEnabled": true,
            "valueLineBalloonEnabled": true,
            "cursorAlpha": 1,
            "cursorColor": couleur,
            "limitToGraph": "g1",
            "valueLineAlpha": 0.2,
            "valueZoomable": true
        },
        "valueScrollbar": {
            "oppositeAxis": false,
            "offset": 50,
            "scrollbarHeight": 10
        },
        "categoryField": "date",
        "categoryAxis": {
            "parseDates": false,
            "dashLength": 1,
            "minorGridEnabled": true
        },
        "export": {
            "enabled": true
        },
        "dataProvider": datas
    });
}


function displayPyramide(idDiagram, labels, datasets) {

    var pyramide = {
        labels: labels,
        datasets: datasets,
    };

    var bar = document.getElementById("diagrm_"+idDiagram).getContext('2d');
    var myBarChart = new Chart(bar, {
        type: "horizontalBar",
        data: pyramide,
        options: {
        responsive: true,
        title: {
            display: false,
        },
        tooltips: {
            intersect: false,
            callbacks: {
                label: (c) => {
                    const value = Number(c.xLabel);
                    //console.log("tool value", c);
                    const positiveOnly = value < 0 ? -value : value;
                    let retStr = "";
                    if (c.datasetIndex === 0) {
                    retStr += `Hommes: ${positiveOnly.toString()}`;
                    } else {
                    retStr += `Femmes: ${positiveOnly.toString()}`;
                    }
                    return retStr;
                },
            },
        },
        legend: {
            position: "top",
        },
        scales: {
            xAxes: [
            {
                // display: true,
                stacked: false,
                ticks: {
                    beginAtZero: true,
                    callback: (v) => {
                         return v < 0 ? -v: v 
                    }
                },
            },
            ],
            yAxes: [
            {
                stacked: true,
                ticks: {
                beginAtZero: true,
                },
                position: "left",
                barPercentage: 0.5,
                categoryPercentage: 1.5,
            }
            ],
        },
        },
    }); 
}

function displayPie(idDiagram, labels, datas, couleurs, couleurs_hover){

    var pieElem = document.getElementById("diagrm_"+idDiagram);
    var data4 = {
        labels: labels,
        datasets: [{
            data: datas,
            backgroundColor: couleurs,
            hoverBackgroundColor: couleurs_hover
        }]
    };

    var myPieChart = new Chart(pieElem, {
        type: 'pie',
        data: data4
    });

}


$(document).ajaxComplete(function(){
    $('a[href="http://www.amcharts.com"]').each(function(){ 
        //var oldUrl = $(this).attr("href"); // Get current url
        //$(this).attr("href", ""); // Set herf value
        $(this).html("")
    });
});