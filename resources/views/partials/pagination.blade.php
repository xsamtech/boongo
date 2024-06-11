
        @if ($lastPage > 1)
                        <div class="pagination-style mt-30">
                            <ul>
                                <li>
                                    <a role="button" class="prev{{ !request()->has('page') ? ' d-none' : '' }}" onclick="event.preventDefault(); window.location.replace('{{ !empty($entity) ? route(\Request::route()->getName(), ['entity' => $entity]) . (request()->has('page') && request()->get('page') != '1' ? '?page=' . request()->get('page') - 1 : '') : route(\Request::route()->getName()) . (request()->has('page') && request()->get('page') != '1' ? '?page=' . request()->get('page') - 1 : '') }}');">
                                        <i class="zmdi zmdi-chevron-left"></i>
                                    </a>
                                </li>
            @if ($lastPage > 5)
                @if (!request()->has('page') || request()->get('page') == 1)
                    @for ($i = (request()->has('page') ? request()->get('page') : 1); $i <= (request()->has('page') ? request()->get('page') + 1 : 2); $i++)
                                <li><a class="{{ request()->get('page') == $i ? 'active disabled' : ($i == 1  && !request()->has('page') ? 'active disabled' : '') }}" href="?page={{ $i }}">{{ $i }}</a></li>
                    @endfor
                                <li><i class="bi bi-three-dots mx-2 fs-2 align-middle text-muted"></i></li>
                                <li><a href="?page={{ $lastPage }}">{{ $lastPage }}</a></li>
                @else
                                <li><a href="?page=1">1</a></li>
                                <li><i class="bi bi-three-dots{{ request()->get('page') > 2 ? '' : ' d-none' }} mx-2 fs-2 align-middle text-muted"></i></li>
                    @for ($i = (request()->has('page') ? (request()->get('page') >= $lastPage - 2 ? $lastPage - 2 : request()->get('page')) : 1); $i <= (request()->has('page') ? (request()->get('page') == $lastPage ? $lastPage : request()->get('page') + 1) : 2); $i++)
                                <li><a class="{{ request()->get('page') == $i ? 'active disabled' : ($i == 1  && !request()->has('page') ? 'active disabled' : '') }}" href="?page={{ $i }}">{{ $i }}</a></li>
                    @endfor
                                <li class="{{ request()->get('page') == $lastPage ? 'd-none' : (request()->get('page') >= $lastPage - 2 ? ' d-none' : '') }}"><i class="bi bi-three-dots mx-2 fs-2 align-middle text-muted"></i></li>
                                <li class="{{ request()->get('page') == $lastPage ? 'd-none' : (request()->get('page') >= $lastPage - 1 ? ' d-none' : '') }}"><a href="?page={{ $lastPage }}">{{ $lastPage }}</a></li>
                @endif
            @else
                @for ($i = 1; $i <= $lastPage; $i++)
                                <li><a class="{{ request()->get('page') == $i ? 'active disabled' : ($i == 1  && !request()->has('page') ? 'active disabled' : '') }}" href="?page={{ $i }}">{{ $i }}</a></li>
                @endfor
            @endif
                                <li>
                                    <a role="button" class="next{{ request()->get('page') == $lastPage ? ' d-none' : '' }}" onclick="event.preventDefault(); window.location.replace('{{ !empty($entity) ? route(\Request::route()->getName(), ['entity' => $entity]) . '?page=' . (request()->has('page') ? request()->get('page') + 1 : request()->get('page') + 2) : route(\Request::route()->getName()) . '?page=' . (request()->has('page') ? request()->get('page') + 1 : request()->get('page') + 2) }}');">
                                        <i class="zmdi zmdi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
        @endif
