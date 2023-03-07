select simulated_bets.created_at as createdDate, games.homeTeam, games.awayTeam,games.commenceTime, c.title,
       gbl.homeTeamSpread as nonSharpHomeSpread, gbl.awayTeamSpread as nonSharpAwaySpread, c2.title, g.homeTeamSpread as sharpHomeSread, g.awayTeamSpread as sharpAwayTeamSpread
from simulated_bets
    join game_betting_lines gbl on simulated_bets.nonSharpBettingLineId = gbl.id
    join game_betting_lines g on g.id = simulated_bets.sharpBettingLineId
    join casinos c on gbl.casinoId = c.id
    join casinos c2 on c2.id = g.casinoId
    join games on gbl.gameId = games.id
where won is null and gbl.isCurrent = 1 and g.isCurrent = 1
and gbl.casinoId in (1,2,16)
and g.casinoId = 4
order by simulated_bets.created_at desc;
