$(document).ready(function () {
    $('#ads-list').DataTable({
        fixedHeader: false,
        fixedColumns: true,
        scrollX: true,
        scrollY: true,
        scrollCollapse: true,
        ordering: false,
        paging: false,
        info: false,
        bPaginate: false,
        search : false,
        fixedColumns: {
            leftColumns: 1,
            heightMatch: 'auto'
        },
        searching: false
    });
});