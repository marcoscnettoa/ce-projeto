<div class="form-group form-group-btn-index">
    @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store") OR $isPublic)
        <a href="{{ URL('/') }}/events/create" class="btn btn-default right form-group-btn-index-cadastrar"><i class="glyphicon glyphicon-plus"></i> Cadastrar</a>
    @endif
</div>

<style>

    .masthead {
        flex-basis: 4rem;
        display: flex;
        align-items: center;
        padding: 0 0.8rem;
        background-color: #0067a3;
        box-shadow: 0 0.1rem 0.1rem rgba(0, 0, 0, 0.1);
    }

    .boards-menu {
        display: flex;
        flex-shrink: 0;
    }

    .boards-btn {
        flex-basis: 9rem;
        font-size: 1.4rem;
        font-weight: 700;
        color: #fff;
        margin-right: 0.8rem;
        padding: 0.6rem 0.8rem;
    }

    .boards-btn-icon {
        font-size: 1.7rem;
        padding-right: 1.2rem;
    }

    .board-search {
        flex-basis: 18rem;
        position: relative;
    }

    .board-search-input {
        height: 3rem;
        border: none;
        border-radius: 0.3rem;
        background-color: #4c94be;
        width: 100%;
        padding: 0 3rem 0 1rem;
        color: #fff;
    }

    .board-search-input:hover {
        background-color: #66a4c8;
    }

    .search-icon {
        font-size: 1.5rem;
        position: absolute;
        top: 50%;
        right: 0.8rem;
        transform: translateY(-50%) rotate(90deg);
        color: #fff;
    }

    .user-settings {
        display: flex;
        height: 3rem;
        color: #fff;
    }

    .user-settings-btn {
        font-size: 1.5rem;
        width: 3rem;
        margin-right: 0.8rem;
    }

    .user-settings-btn:last-of-type {
        margin-right: 0;
    }

    /* Board info bar */

    .board-info-bar {
        flex-basis: 3rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 0.8rem 0;
        padding: 0 1rem;
        color: #f6f6f6;
    }

    .board-controls {
        display: flex;
    }

    .board-controls .btn {
        margin-right: 1rem;
    }

    .board-controls .btn:last-of-type {
        margin-right: 0;
    }

    .board-info-bar .btn {
        font-size: 1.4rem;
        font-weight: 400;
        transition: background-color 150ms;
        padding: 0 0.6rem;
        border-radius: 0.3rem;
        height: 3rem;
    }

    .board-info-bar .btn:hover {
        background-color: #006aa8;
    }

    .private-btn-icon,
    .menu-btn-icon {
        padding-right: 0.6rem;
        white-space: nowrap;
    }

    .board-title h2 {
        font-size: 1.8rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .lists-container {
        display: flex;
        align-items: start;
        overflow-x: auto;
        height: calc(100vh - 8.6rem);
    }

    .list {
        flex: 0 0 27rem;
        display: flex;
        flex-direction: column;
        background-color: #e7edf3;
        max-height: calc(100vh - 11.8rem);
        border-radius: 0.3rem;
        margin-right: 1rem;
    }

    .list:last-of-type {
        margin-right: 0;
    }

    .list-title {
        font-size: 1.2em;
        font-weight: 600;
        color: #172b4d;
        padding-left: 1rem;
    }

    .list-items {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-content: start;
        padding: 0 0.6rem 0.5rem;
        overflow-y: auto;
    }

    .list-items::-webkit-scrollbar {
        width: 1.6rem;
    }

    .list-items::-webkit-scrollbar-thumb {
        background-color: #c4c9cc;
        border-right: 0.6rem solid #e2e4e6;
    }

    .list-items li {
        font-size: 12px;
        font-weight: 400;
        background-color: #fff;
        padding: 0.65rem 0.6rem;
        color: #4d4d4d;
        border-bottom: 0.1rem solid #ccc;
        border-radius: 0.3rem;
        margin-bottom: 0.6rem;
        word-wrap: break-word;
        cursor: pointer;
        font-family: 'Open Sans',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;
        line-height: 1.45;
    }

    .list-items li:hover {
        background-color: #eee;
    }

    .add-card-btn {
        display: block;
        font-size: 1.4rem;
        font-weight: 400;
        color: #838c91;
        padding: 1rem;
        text-align: left;
        cursor: pointer;
    }

    .add-card-btn:hover {
        background-color: #cdd2d4;
        color: #4d4d4d;
        text-decoration: underline;
    }

    .add-list-btn {
        flex: 0 0 27rem;
        display: block;
        font-size: 1.4rem;
        font-weight: 400;
        background-color: #006aa7;
        color: #a5cae0;
        padding: 1rem;
        border-radius: 0.3rem;
        cursor: pointer;
        transition: background-color 150ms;
        text-align: left;
    }

    .add-list-btn:hover {
        background-color: #005485;
    }

    .add-card-btn::after,
    .add-list-btn::after {
        content: '...';
    }

</style>

        <div class="lists-container" style="margin-top: 20px;height: 400px;">

            @foreach($kanban_list as $key => $itens)

                <div class="list">

                    <h3 class="list-title">{{$key}}</h3>

                    <ul class="list-items">

                        @foreach($itens as $value)

                            <a href="{{ URL('/') }}/events/{{$value->id}}/edit">

                                <li>

                                <p style=' white-space: nowrap; ' >{{$value->title}}</p>

                                <p style=' white-space: nowrap; ' data-order="{{ $value->start_date }}">{{(isset($value->start_date)) ? date("d/m/Y H:i", strtotime($value->start_date)) : ""}}</p>

                                <p style=' white-space: nowrap; ' data-order="{{ $value->end_date }}">{{(isset($value->end_date)) ? date("d/m/Y H:i", strtotime($value->end_date)) : ""}}</p>

                                <p style=' white-space: nowrap; ' >{{(isset($value->is_all_day) && $value->is_all_day) ? "Sim" : "Não"}}</p>

                                </li>

                            <a>

                        @endforeach

                    </ul>

                </div>

            @endforeach

        </div>