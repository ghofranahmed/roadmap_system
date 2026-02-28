@if(Auth::check())
    <li class="nav-item">
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="nav-link btn btn-link text-white">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </form>
    </li>
@endif


