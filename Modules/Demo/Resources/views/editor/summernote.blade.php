@extends('demo::layouts.demo_layout')

@section('title', "Summernote编辑器示例")

@section('use_datatables', "true")

@section('head_css')
    <!-- Summernote Plugin CSS -->
    <link href="{{ asset('static/inspinia/v4.0/assets/plugins/summernote/summernote-bs5.min.css') }}" rel="stylesheet">

    <style>
    </style>
@endsection

@section('content')
    <h1>Summernote编辑器示例</h1>

    <form method="POST" id="cherry_form">
        <div class="summernote">
            <h4>Inspinia Admin - Modern Admin Dashboard</h4>
            Inspinia Admin is a powerful and feature-rich Bootstrap-based admin template designed to help you build stunning and functional dashboards. It provides a clean, responsive, and easy-to-use interface for managing data and providing insights. With numerous components and options, it is perfect for building any type of web application.
            <strong>Inspinia Admin</strong> includes everything you need to start building your next project, from user management to charts, tables, and much more. It’s optimized for performance and mobile responsiveness, ensuring a smooth experience on any device.
            <br>
            <br>
            <ul>
                <li>Fully responsive layout</li>
                <li>Customizable UI components</li>
                <li>Built on Bootstrap 4</li>
                <li>Multiple ready-to-use pages</li>
            </ul>
        </div>
    </form>
@endsection

@section('page_js')
    <!-- Summernote Plugin Js -->
    <script src="{{ asset('static/inspinia/v4.0/assets/plugins/summernote/summernote-bs5.min.js') }}"></script>
    <script src="{{ asset('static/inspinia/v4.0/assets/plugins/summernote/lang/summernote-zh-CN.min.js') }}"></script>

    <script>


        $(function () {



            $(".summernote").summernote({
                lang: 'zh-CN', // default: 'en-US'
                height:300,
                toolbar:[
                    ["style",["style"]],
                    ["font",["bold","italic","underline","strikethrough","superscript","subscript","clear"]],
                    ["fontname",["fontname"]],
                    ["fontsize",["fontsize"]],
                    ["color",["color"]],
                    ["para",["ul","ol","paragraph"]],
                    ["height",["height"]],
                    ["table",["table"]],
                    ["insert",["link","picture","video"]],
                    ["view",["fullscreen","codeview","help"]],
                    ["misc",["undo","redo"]]
                ],icons:{
                    magic:"ti ti-wand fs-xl",
                    bold:"ti ti-bold fs-xl",
                    underline:"ti ti-underline fs-xl",
                    eraser:"ti ti-eraser fs-xl",
                    italic:"ti ti-italic fs-xl",
                    strikethrough:"ti ti-strikethrough fs-xl",
                    fontname:"ti ti-font fs-xl",
                    fontsize:"ti ti-text-size fs-xl",
                    color:"ti ti-color-swatch fs-xl",
                    font:"ti ti-typography fs-xl",
                    menuCheck:"ti ti-check fs-xl",
                    unorderedlist:"ti ti-list fs-xl",
                    orderedlist:"ti ti-list-numbers fs-xl",
                    align:"ti ti-align-left fs-xl",
                    alignLeft:"ti ti-align-left fs-xl",
                    alignCenter:"ti ti-align-center fs-xl",
                    alignRight:"ti ti-align-right fs-xl",
                    alignJustify:"ti ti-align-justified fs-xl",
                    alignIndent:"ti ti-indent-increase fs-xl",
                    alignOutdent:"ti ti-indent-decrease fs-xl",
                    table:"ti ti-table fs-xl",
                    link:"ti ti-link fs-xl",
                    picture:"ti ti-photo fs-xl",
                    video:"ti ti-video fs-xl",
                    arrowsAlt:"ti ti-arrows-maximize fs-xl",
                    code:"ti ti-code fs-xl",
                    question:"ti ti-help-circle fs-xl",
                    outdent:"ti ti-indent-decrease fs-xl",
                    indent:"ti ti-indent-increase fs-xl",
                    undo:"ti ti-arrow-back-up fs-xl",
                    redo:"ti ti-arrow-forward-up fs-xl",
                    subscript:"ti ti-subscript fs-xl",
                    superscript:"ti ti-superscript fs-xl"
                },callbacks:{
                    onInit:function(){$(".note-editor .note-btn").each(function(){this.classList.add("btn-light"),this.classList.remove("btn-outline-secondary")})
                    }
                }
            });
        });

    </script>
@endsection
