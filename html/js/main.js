var fetchData = function fetchData() {
    return $.ajax({
        url: "http://api.tune.dev/employees",
        dataType: "json" // defined because nginx is ignoring Content-Type
    });
};

var drawTable = function drawTable() {
    fetchData().done(function(rows) {
        var data = new google.visualization.DataTable();
        data.addColumn('number', 'id');
        data.addColumn('string', 'Employee Name');
        data.addColumn('string', 'Boss Name');
        data.addColumn('number', 'Distance from CEO');
        data.addColumn('number', 'Subordinates');
        data.addRows(rows);

        var dashboard = new google.visualization.Dashboard(document.querySelector('#dashboard'));

        var stringFilter = new google.visualization.ControlWrapper({
            controlType: 'StringFilter',
            containerId: 'name_filter',
            options: {
                filterColumnIndex: 1
            }
        });

        var table = new google.visualization.ChartWrapper({
            chartType: 'Table',
            containerId: 'table',
            options: {
                showRowNumber: false,
                width: '100%',
                height: '100%',
                page: 'enable',
                pageSize: '100',
                sortColumn: 0
            }
        });

        dashboard.bind([stringFilter], [table]);
        dashboard.draw(data);
    });
};

var loadTable = function loadTable() {
    // Load the Visualization API and the controls/table package.
    google.load('visualization', '1.1', {'packages':['controls']});

    // Set a callback to run when the Google Visualization API is loaded.
    google.setOnLoadCallback(drawTable);
};
