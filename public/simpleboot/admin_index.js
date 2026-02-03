(function(){

    /* 设备终端 - Dark Theme */
    var echarts_source = echarts.init(document.getElementById('echarts_source'));
    var echarts_source_option = {
        backgroundColor: 'transparent',
        tooltip : {
            trigger: 'item',
            formatter: "{c}",
            backgroundColor: '#111',
            borderColor: '#333',
            textStyle: { color: '#fff' }
        },
        legend: {
            left: 'center',
            top:'bottom',
            itemWidth:10,
            itemHeight:10,
            formatter: '{name}',
            itemGap:50,
            textStyle:{
                color: '#888',
                fontSize:14
            },
            data:data_source.name
        },
        calculable : true,
        series : [
            {
                type:'pie',
                radius :  ['50%', '70%'],
                center: ['50%', '50%'],
                label:{
                    show:true,
                    textStyle : {
                        fontWeight : 300,
                        fontSize : 14,
                        color: '#fff'
                    },
                    formatter:'{d}%'
                },
                data:data_source.v_n
            }
        ]
    };
    echarts_source.setOption(echarts_source_option);

    /* 注册渠道 - Dark Theme */
    var echarts_reg = echarts.init(document.getElementById('echarts_reg'));
    var echarts_reg_option = {
        backgroundColor: 'transparent',
        tooltip : {
            trigger: 'auto',
            axisPointer : {
                type : 'shadow'
            },
            backgroundColor: '#111',
            borderColor: '#333',
            textStyle: { color: '#fff' }
        },
        xAxis : [
            {
                type : 'category',
                data : data_type.name,
                nameTextStyle:{
                    color: '#888',
                    padding:[3,0,0,0],
                    fontSize:14
                },
                axisLabel: {
                    color: '#888'
                },
                axisLine: {
                    lineStyle: { color: '#333' }
                },
                axisTick: {
                    alignWithLabel: true,
                    lineStyle: { color: '#333' }
                }
            }
        ],
        yAxis : [
            {
                max:'100',
                type : 'value',
                axisLabel: {
                    show: true,
                    interval: 'auto',
                    color:'#666',
                    formatter: '{value}%'
                },
                axisLine: {
                    lineStyle: { color: '#333' }
                },
                splitLine: {
                    lineStyle: { color: '#222' }
                }
            }
        ],
        series : [
            {
                type:'bar',
                barWidth: '60%',
                data:data_type.nums_per,
                color: function (params){
                    var colorList = data_type.color;
                    return colorList[params.dataIndex];
                },
                label: {
                    show: true,
                    position: 'top',
                    formatter: '{c}%',
                    textStyle: {
                        color: '#fff',
                        fontSize: 14
                    }
                }
            }
        ]
    };
    echarts_reg.setOption(echarts_reg_option);

    /* 七天数据 - Dark Theme */
    var echarts_week = echarts.init(document.getElementById('echarts_week'));
    var echarts_week_option = {
        backgroundColor: 'transparent',
        legend: {
            left: 'right',
            itemWidth:10,
            itemHeight:10,
            formatter: '{name}',
            itemGap:30,
            textStyle:{
                color: '#888',
                fontSize:12
            }
        },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: data_week.date,
            axisLabel: { color: '#666' },
            axisLine: { lineStyle: { color: '#333' } }
        },
        yAxis: {
            type: 'value',
            minInterval:'1',
            axisLabel: { color: '#666' },
            axisLine: { lineStyle: { color: '#333' } },
            splitLine: { lineStyle: { color: '#222' } }
        },
        tooltip : {
            trigger: 'axis',
            backgroundColor: '#111',
            borderColor: '#333',
            textStyle: { color: '#fff' },
            axisPointer: {
                type: 'cross',
                label: {
                    backgroundColor: '#333'
                }
            }
        },
        series: [{
            name:'新增视频',
            data: data_week.value,
            type: 'line',
            smooth: true,
            lineStyle:{
                color:'#667eea'
            },
            itemStyle: {
                color: '#667eea',
            },
            symbolSize:8,
            areaStyle: {
                color: {
                    type: 'linear',
                    x: 0, y: 0, x2: 0, y2: 1,
                    colorStops: [{
                        offset: 0, color: 'rgba(102,126,234,0.4)'
                    }, {
                        offset: 1, color: 'rgba(102,126,234,0)'
                    }],
                    global: false
                }
            }
        },{
            name:'点赞数',
            data: data_week.likes,
            type: 'line',
            smooth: true,
            lineStyle:{
                color:'#f5576c'
            },
            itemStyle: {
                color: '#f5576c',
            },
            symbolSize:8,
            areaStyle: {
                color: {
                    type: 'linear',
                    x: 0, y: 0, x2: 0, y2: 1,
                    colorStops: [{
                        offset: 0, color: 'rgba(245,87,108,0.4)'
                    }, {
                        offset: 1, color: 'rgba(245,87,108,0)'
                    }],
                    global: false
                }
            }
        },{
            name:'粉丝数',
            data: data_week.fans,
            type: 'line',
            smooth: true,
            lineStyle:{
                color:'#43e97b'
            },
            itemStyle: {
                color: '#43e97b',
            },
            symbolSize:8,
            areaStyle: {
                color: {
                    type: 'linear',
                    x: 0, y: 0, x2: 0, y2: 1,
                    colorStops: [{
                        offset: 0, color: 'rgba(67,233,123,0.4)'
                    }, {
                        offset: 1, color: 'rgba(67,233,123,0)'
                    }],
                    global: false
                }
            }
        }]
    };
    echarts_week.setOption(echarts_week_option);

    /* 广告数据 - Dark Theme */
    var echarts_ad = echarts.init(document.getElementById('echarts_ad'));
    var echarts_ad_option = {
        backgroundColor: 'transparent',
        legend: {
            left: 'right',
            itemWidth:10,
            itemHeight:10,
            formatter: '{name}',
            itemGap:30,
            textStyle:{
                color: '#888',
                fontSize:12
            },
            data:['广告数量','浏览数量']
        },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: data_ad.date,
            axisLabel: { color: '#666' },
            axisLine: { lineStyle: { color: '#333' } }
        },
        yAxis: {
            type: 'value',
            minInterval:'1',
            axisLabel: { color: '#666' },
            axisLine: { lineStyle: { color: '#333' } },
            splitLine: { lineStyle: { color: '#222' } }
        },
        tooltip : {
            trigger: 'axis',
            backgroundColor: '#111',
            borderColor: '#333',
            textStyle: { color: '#fff' },
            axisPointer: {
                type: 'cross',
                label: {
                    backgroundColor: '#333'
                }
            }
        },
        series: [{
            name:'广告数量',
            data: data_ad.value,
            type: 'line',
            smooth: true,
            symbol:'circle',
            symbolSize: 6,
            lineStyle:{
                color:'#8e2de2'
            },
            itemStyle: {
                color: '#8e2de2',
            }
        },
        {
            name:'浏览数量',
            data: data_ad.videoviews,
            type: 'line',
            smooth: true,
            symbol:'circle',
            symbolSize: 6,
            lineStyle:{
                color:'#f5576c'
            },
            itemStyle: {
                color: '#f5576c',
            }
        }]
    };
    echarts_ad.setOption(echarts_ad_option);


    /* ajax */
    function getData(request_data){
        $.ajax({
            url:'/index.php?g=admin&m=Main&a=getdata',
            type:'POST',
            data:request_data,
            dataType:'json',
            success:function(data){
                var code=data.status;
                var info=data.info;
                if(code!=1){
                    alert(info);
                    return !1;
                }

                var action=request_data.action;
                switch(action){
                    case '1':
                        $(".basic_list li.newusers .basic_list_n span").text(info.newusers);
                        $(".basic_list li.launches .basic_list_n span").text(info.launches);
                        $(".basic_list li.durations .basic_list_n span").text(info.durations);
                        $(".basic_list li.activityusers .basic_list_n span").text(info.activeusers);
                        break;
                    case '2':
                        echarts_week_option.xAxis.data=[];
                        echarts_week_option.series[0].data=[];
                        echarts_week.setOption(echarts_week_option);
                        break;
                    case '3':
                        echarts_ad_option.xAxis.data=info.date;
                        echarts_ad_option.series[0].data=info.value;
                        echarts_ad_option.series[1].data=info.videoviews;
                        echarts_ad.setOption(echarts_ad_option);
                        break;
                }
            },
            error:function(){

            }
        })
    }


    $(".search").click(function(){
        var _this=$(this);
        var start_time=_this.parents('.bd_title').find("input[name=start_time]").val();
        var end_time=_this.parents('.bd_title').find("input[name=end_time]").val();
        var action=_this.parents('.bd_title').find(".action").val();
        var request_data={action:action,start_time:start_time,end_time:end_time};
        getData(request_data);
    })


    $(".export").click(function(){
        var _this=$(this);
        var action=_this.parents('.bd_title').find(".action").val();
        var start_time=_this.parents('.bd_title').find("input[name=start_time]").val();
        var end_time=_this.parents('.bd_title').find("input[name=end_time]").val();
        location.href='/index.php?g=admin&m=Main&a=export&action='+action+'&start_time='+start_time+'&end_time='+end_time;
    })

})()
