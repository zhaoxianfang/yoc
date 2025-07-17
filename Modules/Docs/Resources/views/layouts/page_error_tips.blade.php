<div class="alert-fixed-container">
    @if (count($errors??[]) > 0)
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger alert-dismissable">
                <span class="alert-message">{{ $error }}</span>
                <button class="alert-close">&times;</button>
            </div>
        @endforeach
    @endif
    @if (session('success'))
    <div class="alert alert-success alert-dismissable">
        <span class="alert-message">{{ session('success') }}</span>
        <button class="alert-close">&times;</button>
    </div>
    @endif
    @if (session('error'))
    <div class="alert alert-danger alert-dismissable">
        <span class="alert-message">{{ session('error') }}</span>
        <button class="alert-close">&times;</button>
    </div>
    @endif
    @if (session('warning'))
    <div class="alert alert-warning alert-dismissable">
        <span class="alert-message">{{ session('warning') }}</span>
        <button class="alert-close">&times;</button>
    </div>
    @endif
    @if (session('info'))
    <div class="alert alert-info alert-dismissable">
        <span class="alert-message">{{ session('info') }}</span>
        <button class="alert-close">&times;</button>
    </div>
    @endif
</div>
