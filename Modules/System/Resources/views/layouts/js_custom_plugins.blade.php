
@hasSection('use_datepicker')
    <!-- Daterangepicker Plugin Js -->
    <script src="{{ asset('static/inspinia/v4.0/assets/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('static/inspinia/v4.0/assets/plugins/daterangepicker/daterangepicker.js') }}"></script>

    <script type="text/javascript">
        $(function () {

            $('.input-group .date').daterangepicker({
                singleDatePicker: true,      // 单日期模式
                showDropdowns: true,        // 显示年/月下拉选择
                minYear: 1901,              // 最小年份
                maxYear: 2099, // 最大年份=当前年份
                locale: {
                    format: 'YYYY-MM-DD',       // 日期格式
                    applyLabel: '确定',         // 确定按钮文字
                    cancelLabel: '取消',         // 取消按钮文字
                    fromLabel: '从',            // 开始日期标签
                    toLabel: '至',              // 结束日期标签
                    customRangeLabel: '自定义',  // 自定义范围选项
                    daysOfWeek: ['日', '一', '二', '三', '四', '五', '六'], // 星期缩写
                    monthNames: [               // 月份名称
                        '一月', '二月', '三月', '四月', '五月', '六月',
                        '七月', '八月', '九月', '十月', '十一月', '十二月'
                    ],
                    firstDay: 1                 // 每周第一天（1=星期一）
                },
                // startDate: $(this).val() ? moment($(this).val(), 'YYYY-MM-DD') : moment(), // 非空时解析，空时默认今天
                // autoUpdateInput: true,        // 自动更新输入框值
                autoUpdateInput: false,        // 自动更新输入框值
            }, function(start, end, label, picker) {
                // autoUpdateInput: true 时此回调才有效
                $(this).val(start.format('YYYY-MM-DD'));
            });
            // 当日期范围被选择时的事件处理
            $('.input-group .date').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD'));
            });
        })
    </script>
@endif

@hasSection('use_datatables')
    <script src="{{ asset('static/libs/DataTables/DataTables-2.3.3/datatables.min.js') }}"></script>

    <!-- 时间 -->
    <script src="{{ asset('static/libs/daterangepicker/moment.min.js') }}"></script>
    <script src="{{ asset('static/libs/daterangepicker/daterangepicker.min.js') }}"></script>

    <script src="{{ asset('static/libs/zxf/js/data_tables/data_tables.min.js') }}"></script>
@endif

@hasSection('use_form')
    <script src="{{ asset('static/libs/zxf/modal/modal.min.js') }}" charset="utf-8"></script>
    <script src="{{ asset('static/libs/zxf/js/tools.min.js') }}" type='text/javascript'></script>
@endif


@section('page_js')
    @hasSection('page_js')
        <!-- 页面中引入page js 「保持」放在此文件的最后 -->
    @endif
@show
