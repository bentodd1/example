
@extends('app')

@section('content')


    <!-- TODO: Current Tasks -->
    <!-- Current Tasks -->
        <div class="panel panel-default">
            <p></p>

            <div class="panel-body">

                <!-- Table Body -->

               <table class="table table-striped task-table">

                  <!-- Table Headings -->
                    <thead>
                    <th>&nbsp; Current Simulated Bets</th>
                    </thead>
                    <p></p>

                    <!-- Table Body -->
                    <tbody>
                    @foreach ($simulatedBets as $simulatedBet)
                        <tr>
                            <!-- Task Name -->
                            <td class="table-text">
                                BET
                               <div>{{ $simulatedBet->sharpLine->casino->title   }}</div>
                                <div>{{$simulatedBet->sharpLine->game->homeTeam }} {{$simulatedBet->sharpLine->homeTeamSpread}}</div>
                                <div>{{$simulatedBet->sharpLine->game->awayTeam }} {{$simulatedBet->sharpLine->awayTeamSpread}}</div>
                                <div>{{ $simulatedBet->nonSharpLine->casino->title }} </div>
                                <div> {{$simulatedBet->nonSharpLine->game->homeTeam }} {{$simulatedBet->nonSharpLine->homeTeamSpread}}</div>
                                <div>{{$simulatedBet->nonSharpLine->game->awayTeam }} {{$simulatedBet->nonSharpLine->awayTeamSpread}}</div>
                                <div>-------------------------------------<div>

                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
@endsection

