select games.homeTeam, games.awayTeam,games.commenceTime, c.title,
       gbl.homeTeamSpread, gbl.awayTeamSpread, c2.title, g.homeTeamSpread, g.awayTeamSpread
from simulated_bets
    join game_betting_lines gbl on simulated_bets.nonSharpBettingLineId = gbl.id
    join game_betting_lines g on g.id = simulated_bets.sharpBettingLineId
    join casinos c on gbl.casinoId = c.id
    join casinos c2 on c2.id = g.casinoId
    join games on gbl.gameId = games.id
where won is null and gbl.isCurrent = 1 and g.isCurrent = 1
and gbl.casinoId in (1,2,16)
order by simulated_bets.created_at desc;
