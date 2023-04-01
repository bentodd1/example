select simulated_bets.won, g.homeTeam, g.awayTeam, g.commenceTime, gbl.homeTeamSpread, gbl2.homeTeamSpread, s.* from simulated_bets
                                                                                                                         join game_betting_lines gbl on gbl.id = simulated_bets.sharpBettingLineId
                                                                                                                         join game_betting_lines gbl2 on gbl2.id = simulated_bets.nonSharpBettingLineId
                                                                                                                         join scores s on simulated_bets.scoreId = s.id
                                                                                                                         join games g on gbl.gameId = g.id
where gbl.casinoId =7
  and g.sportId = 4
group by s.gameId nl;
