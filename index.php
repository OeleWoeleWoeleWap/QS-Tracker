<?php

    $con = mysql_connect("localhost","Adwords","tSy92WTqsVsDLh9e") or die('MySQL error: '.mysql_error());
    $db = mysql_select_db("Adwords_qs_tracker") or die("MySQL error: ".mysql_error());
?>

<html>
<head>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <title>Quality Score tracker</title>
</head>
<body>
    <h2>Quality Score Tracker</h2>
    <div id='clientheader'></div>
    <div id='campaignheader'></div>
    <div id='adgroupheader'></div>

    <div id='table_container'></div>

           


    <div id='overlayer'>
        <div id='box'><table action='store_data.php' id='adForm'>
            <tr><td>Client</td><td><input type='text' name='client' id='adFormClient' disabled /></td></tr>
            <tr><td>Campaign</td><td><input type='text' name='campaign' id='adFormCampaign' disabled /></td></tr>
            <tr><td>Adgroup</td><td><input type='text' name='adgroup' id='adFormAdgroup' disabled /></td></tr>
            <tr><td>Keyword</td><td><input type='text' name='keyword' id='adFormKeyword' disabled /></td></tr>
            <tr><td>Quality Score</td><td><input type='text' name='qs' id='adFormQS' /></td></tr>
            <tr><td>Jaar</td><td><input type='text' name='jaar' id='adFormYear' /></td></tr>
            <tr><td>Month (1-12)</td><td><input type='text' name='week' id='adFormMonth' /></td></tr>
            <tr><td></td><td><input type='submit' id='adFormSubmit' /></td></tr>
        </table></div>
    </div>

    <script type="text/javascript">
    var date = new Date;
    var numcols = 13;
    

    fillTable('undefined','undefined','undefined');

    function fillTable(client,campaign,adgroup){
        var surl = "fill_account.php?numcols="+numcols;
        var header = 'Client';
        $('#table_container').css('margin-left','0px').empty();
        $('#clientheader, #campaignheader, #adgroupheader').hide();

        $('#table_container').text('Loading...');

        if(client != 'undefined'){
            $('#table_container').css('margin-left','0px').empty();
            surl = "fill_client.php?numcols="+numcols+"&Client="+client;
            header = 'Campaign';
            $('#clientheader').show();
        }
        if(campaign != 'undefined'){
            $('#table_container').css('margin-left','15px').empty();
            surl = "fill_campaign.php?numcols="+numcols+"&Client="+client+"&campaign="+campaign;
            header = 'Adgroup';
            $('#campaignheader').show();
        }
        if(adgroup != 'undefined') {
            $('#table_container').css('margin-left','30px').empty();
            surl = "fill_adgroup.php?numcols="+numcols+"&Client="+client+"&campaign="+campaign+"&adgroup="+adgroup;
            header = 'Keyword';
            $('#adgroupheader').show();
        }
        $.ajax({url:surl,success:function(result){
            var result = eval('['+result+']');
            console.log(result)
            drawTable(result[0],header);
        }});
    }

    $('h2').eq(0).click(function(){
        fillTable('undefined','undefined','undefined');
    });

    $('#clientheader').click(function(){
        var client = $('#clientheader').attr('client')
        fillTable(client,'undefined','undefined');
    });

    $('#campaignheader').click(function(){
        var client = $('#clientheader').attr('client');
        var campaign = $(this).attr('campaign');
        fillTable(client,campaign,'undefined');
    });

   $('body').click(function(event) {
        if(event.target.className == 'client'){
            var client = event.target.parentNode.firstChild.getAttribute('client');
            $('#clientheader').show().html("<div class='arrow-down'></div>"+client).attr('client',client);
            $('#campaignheader, #adgroupheader').hide();
            $('#table_container').css('margin-left','0px').empty();
            fillTable(client,'undefined','undefined')

        }
        else if(event.target.className == 'campaign'){
            var client = event.target.parentNode.firstChild.getAttribute('client');
            var campaign = event.target.parentNode.firstChild.getAttribute('campaign');

            $('#campaignheader').show().html("<div class='arrow-down'></div>"+campaign).attr('campaign',campaign);
            $('#adgroupheader').hide();
            $('#table_container').css('margin-left','15px').empty();

            fillTable(client,campaign,'undefined');
        }
        else if(event.target.className == 'adgroup'){
            var client = event.target.parentNode.firstChild.getAttribute('client');
            var campaign = event.target.parentNode.firstChild.getAttribute('campaign');
            var adgroup = event.target.parentNode.firstChild.getAttribute('adgroup');

            $('#adgroupheader').show().html("<div class='arrow-down'></div>"+adgroup).attr('adgroup',adgroup);

            $('#table_container').css('margin-left','30px').empty();
            fillTable(client,campaign,adgroup)
        }
        else if(event.target.className == 'adData'){
            $('#overlayer').show();

            var client = event.target.parentNode.firstChild.getAttribute('client');
            var campaign = event.target.parentNode.firstChild.getAttribute('campaign');
            var adgroup = event.target.parentNode.firstChild.getAttribute('adgroup');
            var keyword = event.target.parentNode.firstChild.getAttribute('keyword');

            $('#adFormClient').val(client);
            $('#adFormCampaign').val(campaign || 'Handmatig toegevoegde data');
            $('#adFormAdgroup').val(adgroup || 'Handmatig toegevoegde data');
            $('#adFormKeyword').val(keyword || 'Handmatig toegevoegde data');
        }
    });


    $('#adFormSubmit').click(function(e){
        e.preventDefault();
        var client = $('#adFormClient').val();
        var campaign = $('#adFormCampaign').val();
        var adgroup = $('#adFormAdgroup').val();
        var keyword = $('#adFormKeyword').val();
        var qs = $('#adFormQS').val();
        var impr = $('#adFormImp').val();
        var month = $('#adFormMonth').val();
        var year = $('#adFormYear').val();

        var surl = $('#adForm').attr('action');
        surl += '?client='+client;
        surl += '&campaign='+campaign;
        surl += '&adGroup='+adgroup;
        surl += '&keywd='+keyword;
        surl += '&qs='+qs;
        surl += '&impressions=1';
        surl += '&year='+year;
        surl += '&month='+month;
        $.ajax({url:surl,success:function(result){
            $('#overlayer').hide();
            $('#adFormQS').val('');
            $('#adFormYear').val('');
            $('#adFormMonth').val('');
            console.log(result);

        }});


    })

    $('#overlayer').click(function(e){
        if(e.target.getAttribute('id') == 'overlayer')
            $(this).hide();
    })

    google.load("visualization", "1", {packages:["table"]});
    function drawTable(data2,type) {
        var data = new google.visualization.DataTable();
        data.addColumn('string', type);
        for(var i=0;i<numcols;i++)
            data.addColumn('string',makeDate(numcols - i));
        
        data.addRows(data2);

        var table = new google.visualization.Table(document.getElementById('table_container'));

        table.draw(data, {showRowNumber: false,allowHtml: true});
      }

    function makeDate(back){
        var months = ['Jan','Feb','Mrt','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Dec'];
        var res = '<center> ';
        var weeknumber = date.getMonth() - back;
        
        if(weeknumber < 0){
            weeknumber += 12;
        }
        res += months[weeknumber];
        //res += '-';
        //res += year;
        return res+'</center>';

    }

    </script>

</body>
</html>