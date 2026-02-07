<?php
header("Access-Control-Allow-Origin: https://Sol-0203.com");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
date_default_timezone_set('Asia/Kolkata');
$serviceNowTimeFormatted = date('Y-m-d H:i:s');

$jsonData = '{
    "data": {
        "popular": {
            "platformList": [
            
                 {
                    "vendorId": "23",
                    "vendorCode": "TB_Chess",
                    "gameCode": "2126c5c458316ba1f2df65b387b60408",
                    "gameNameEn": "Chiken Road",
                    "imgUrl": "https://luckmedia.link/iog_chicken_road/thumb_3_4_custom.webp",
                    "imgUrl2": "https://luckmedia.link/iog_chicken_road/thumb_3_4_custom.webp",
                    "winOdds": 97.90
                },
                {
                    "vendorId": "23",
                    "vendorCode": "TB_Chess",
                    "gameCode": "562b299961b0ec40f252a832453c67b0",
                    "gameNameEn": "Chiken Road 2",
                    "imgUrl": "https://luckmedia.link/iog_chicken_road_2/thumb_3_4_custom.webp",
                    "imgUrl2": "https://luckmedia.link/iog_chicken_road_2/thumb_3_4_custom.webp",
                    "winOdds": 97.90
                },
                {
                    "vendorId": "23",
                    "vendorCode": "TB_Chess",
                    "gameCode": "a04d1f3eb8ccec8a4823bdf18e3f0e84",
                    "gameNameEn": "Aviator",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/SPRIBE/aviator_20250210120506414.png",
                    "imgUrl2": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/800_20250210113151154.png",
                    "winOdds": 97.90
                },
                {
                    "vendorId": "23",
                    "vendorCode": "TB_Chess",
                    "gameCode": "5c4a12fb0a9b296d9b0d5f9e1cd41d65",
                    "gameNameEn": "Mines",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/100.png",
                    "imgUrl2": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/100_20250210102610370.png",
                    "winOdds": 96.26
                },
                {
                    "vendorId": "23",
                    "vendorCode": "TB_Chess",
                    "gameCode": "c68a515f0b3b10eec96cf6d33299f4e2",
                    "gameNameEn": "Goal",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/105_20250210113618898.png",
                    "imgUrl2": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/105_20250210113618945.png",
                    "winOdds": 97.64
                },
                {
                    "vendorId": "23",
                    "vendorCode": "TB_Chess",
                    "gameCode": "8a87aae7a3624d284306e9c6fe1b3e9c",
                    "gameNameEn": "Dice",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/102_20250210111705541.png",
                    "imgUrl2": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/102_20250210102559317.png",
                    "winOdds": 96.13
                },
                {
                    "vendorId": "23",
                    "vendorCode": "TB_Chess",
                    "gameCode": "6ab7a4fe5161936012d6b06143918223",
                    "gameNameEn": "Plinko",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/103_20250210111953841.png",
                    "imgUrl2": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/103_20250210102957755.png",
                    "winOdds": 97.94
                },
                {
                    "vendorId": "23",
                    "vendorCode": "TB_Chess",
                    "gameCode": "a669c993b0e1f1b7da100fcf95516bdf",
                    "gameNameEn": "Hilo",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/101_20250210111652965.png",
                    "imgUrl2": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/101_20250210102547898.png",
                    "winOdds": 97.19
                }
            ],
            "clicksTopList": [
                {
                    "vendorId": "18",
                    "vendorCode": "JILI",
                    "gameCode": "4bceeb28b1a88c87d1ef518d7af2bba9",
                    "gameNameEn": "Money Coming",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/51.png",
                    "imgUrl2": "",
                    "winOdds": 96.65
                },
                {
                    "vendorId": "18",
                    "vendorCode": "JILI",
                    "gameCode": "8488c76ee2afb8077fbd7eec62721215",
                    "gameNameEn": "Fortune Gems",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/109.png",
                    "imgUrl2": "",
                    "winOdds": 97.02
                },
               
                {
                    "vendorId": "18",
                    "vendorCode": "JILI",
                    "gameCode": "b560b7c42bd29f7d0cda06485a3c4af5",
                    "gameNameEn": "SevenSevenSeven",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/27.png",
                    "imgUrl2": "",
                    "winOdds": 97.91
                },
                {
                    "vendorId": "18",
                    "vendorCode": "JILI",
                    "gameCode": "984615c9385c42b3dad0db4a9ef89070",
                    "gameNameEn": "Charge Buffalo",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/47.png",
                    "imgUrl2": "",
                    "winOdds": 96.43
                },
                {
                    "vendorId": "18",
                    "vendorCode": "JILI",
                    "gameCode": "e794bf5717aca371152df192341fe68b",
                    "gameNameEn": "Royal Fishing",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/1.png",
                    "imgUrl2": "",
                    "winOdds": 97.29
                },
                {
                    "vendorId": "18",
                    "vendorCode": "JILI",
                    "gameCode": "8c62471fd4e28c084a61811a3958f7a1",
                    "gameNameEn": "Crazy777",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/35.png",
                    "imgUrl2": "",
                    "winOdds": 97.78
                },
                {
                    "vendorId": "18",
                    "vendorCode": "JILI",
                    "gameCode": "bdfb23c974a2517198c5443adeea77a8",
                    "gameNameEn": "Super Ace",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/49.png",
                    "imgUrl2": "",
                    "winOdds": 97.28
                },
                {
                    "vendorId": "17",
                    "vendorCode": "EVO_Electronic",
                    "gameCode": "bb91c454a573010ceeba84dc89e253f4",
                   "gameNameEn": "Footballr Scratch",
                    "imgUrl": "https://felobet.in/felobet/game-icon/footballscratch.png",
                    "imgUrl2": "",
                    "winOdds": 97.12
                },
                {
                    "vendorId": "6",
                    "vendorCode": "JDB",
                    "gameCode": "4042e5d0c777e1d3c3bd481dac0a867e",
                    "gameNameEn": "Super Niubi Deluxe",
                    "imgUrl": "https://apiprovider.codesellr.com/jdbimagesweb/14045.png",
                    "imgUrl2": "",
                    "winOdds": 96.10
                },
                {
                    "vendorId": "19",
                    "vendorCode": "Card365",
             
                    "gameNameEn": "3 Patti Classic",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/Card365/707_20250210142254071.png",
                    "imgUrl2": "",
                    "winOdds": 96.17
                },
                {
                    "vendorId": "18",
                    "vendorCode": "JILI",
                    "gameCode": "71c68a4ddb63bdc8488114a08e603f1c",
                    "gameNameEn": "Happy Fishing",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/82.png",
                    "imgUrl2": "",
                    "winOdds": 96.90
                },
                {
                    "vendorId": "18",
                    "vendorCode": "JILI",
                    "gameCode": "3cf4a85cb6dcf4d8836c982c359cd72d",
                    "gameNameEn": "Jack Pot Fishing",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/32.png",
                    "imgUrl2": "",
                    "winOdds": 96.34
                },
                {
                    "vendorId": "4",
                    "vendorCode": "MG",
                    
                    "gameNameEn": "Wildfire Wins",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/MG/SMG_wildfireWins.png",
                    "imgUrl2": "",
                    "winOdds": 96.38
                },
                {
                    "vendorId": "18",
                    "vendorCode": "JILI",
                    "gameCode": "caacafe3f64a6279e10a378ede09ff38",
                    "gameNameEn": "Mega Fishing",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/74.png",
                    "imgUrl2": "",
                    "winOdds": 97.79
                },
                {
                    "vendorId": "18",
                    "vendorCode": "JILI",
                    "gameCode": "c3f86b78938eab1b7f34159d98796e88",
                    "gameNameEn": "Golden Bank",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/45.png",
                    "imgUrl2": "",
                    "winOdds": 97.18
                },
                {
                    "vendorId": "19",
                    "vendorCode": "Card365",
                   
                    "gameNameEn": "Rummy",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/Card365/710_20250210142312293.png",
                    "imgUrl2": "",
                    "winOdds": 96.95
                },
                {
                    "vendorId": "19",
                    "vendorCode": "Card365",
                   
                    "gameNameEn": "Three Pictures",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/Card365/563_20250210142918946.png",
                    "imgUrl2": "",
                    "winOdds": 97.63
                }
            ],
            "clicksVideoTopList": [
                {
                    "vendorId": "38",
                    "vendorCode": "MG_Video",
                  
                    "gameNameEn": "Auto Roulette ",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Video/SMG_titaniumLiveGamesAutoRoulette.png",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "38",
                    "vendorCode": "MG_Video",
                   
                    "gameNameEn": "Roulette ",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Video/SMG_titaniumLiveGames_Roulette.png",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "38",
                    "vendorCode": "MG_Video",
                   
                    "gameNameEn": "Sicbo",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Video/SMG_titaniumLiveGames_Sicbo.png",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "38",
                    "vendorCode": "MG_Video",
                  
                    "gameNameEn": "Bonus Baccarat",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Video/SMG_titaniumLiveGames_Baccarat.png",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "10",
                    "vendorCode": "AG_Video",
                   
                    "gameNameEn": null,
                    "imgUrl": "",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "10",
                    "vendorCode": "AG_Video",
                    
                    "gameNameEn": null,
                    "imgUrl": "",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "10",
                    "vendorCode": "AG_Video",
                   
                    "gameNameEn": null,
                    "imgUrl": "",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "10",
                    "vendorCode": "AG_Video",
                    
                    "gameNameEn": null,
                    "imgUrl": "",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "10",
                    "vendorCode": "AG_Video",
                    
                    "gameNameEn": null,
                    "imgUrl": "",
                    "imgUrl2": "",
                    "winOdds": 0.0
                }
            ]
        },
"sport": [
            {
               "gameID": "92b24e4c25107367a80e0fe1a97c24e4",
                "slotsTypeID": 25,
                "slotsName": "Wickets9",
                "vendorId": 25,
                "vendorCode": "Wickets9",
                "state": 1,
                "vendorImg": "https://ossimg.tashanedc.com/Tashanwin/vendorlogo/vendorlogo_2025032917372331c9.png"
            },
            {
               "gameID": "c4b2813f6bbc5abf502ddfb857e604eb",
                "slotsTypeID": 8,
                "slotsName": "CMD",
                "vendorId": 8,
                "vendorCode": "CMD",
                "state": 1,
                "vendorImg": "https://ossimg.tashanedc.com/Tashanwin/vendorlogo/vendorlogo_202503291809584w6e.png"
            },
            {
               "gameID": "08ced9dd788aed11ff3c7f387ae0f063",
                "slotsTypeID": 14,
                "slotsName": "SaBa",
                "vendorId": 14,
                "vendorCode": "SaBa",
                "state": 1,
                "vendorImg": "https://ossimg.tashanedc.com/Tashanwin/vendorlogo/vendorlogo_20250329173656ob4x.png"
            }
           
        ],
        "video": [
              {
                "slotsTypeID": 16,
                "slotsName": "EVO_Video",
                "vendorId": 16,
                "gameCode": "367c395a50d4ef9edda332e17094670b",
                "state": 1,
                "vendorImg": "https:\/\/ossimg.jalwa-jalwa.com\/Jalwa\/vendorlogo\/vendorlogo_20250311105326ntuv.png"
            },
            {
                "slotsTypeID": 16,
                "slotsName": "Pragmatic Play",
                "vendorId": 16,
                "gameCode": "a1a01855da3f29d09a848e705ad76ea1",
                "state": 1,
                "vendorImg": "https:\/\/tse1.mm.bing.net\/th\/id\/OIP.gJGZtIEgw-wZ7K2i4H_LQAHaHa?rs=1&pid=ImgDetMain&o=7&rm=3"
            },
            {
                "slotsTypeID": 27,
                "slotsName": "SEXY_Video",
                "vendorId": 27,
                "gameCode": "5956fee9c7e1524f0e6310e75a368c81",
                "state": 1,
                "vendorImg": "https:\/\/ossimg.jalwa-jalwa.com\/Jalwa\/vendorlogo\/vendorlogo_202503111054418bsk.png"
            },
            {
                "slotsTypeID": 7,
                "slotsName": "DG",
                "vendorId": 7,
                "gameCode": "8737e1ef982bd7ba41ec02c1823626f9",
                "state": 1,
                "vendorImg": "https:\/\/ossimg.jalwa-jalwa.com\/Jalwa\/vendorlogo\/vendorlogo_20250311105152d49l.png"
            },
            {
                "slotsTypeID": 10,
                "slotsName": "AG_Video",
                "vendorId": 10,
                "gameCode": "38d36d194ec3b610e49904bf06bbaa68",
                "state": 1,
                "vendorImg": "https:\/\/ossimg.jalwa-jalwa.com\/Jalwa\/vendorlogo\/vendorlogo_2025031110524549u1.png"
            },
            {
                "slotsTypeID": 26,
                "slotsName": "WM_Video",
                "vendorId": 26,
                "gameCode": "3630a6a3c836afa6864578ef21f8fa93",
                "state": 1,
                "vendorImg": "https:\/\/ossimg.jalwa-jalwa.com\/Jalwa\/vendorlogo\/vendorlogo_20250311105431knjh.png"
            },
            {
                "slotsTypeID": 38,
                "slotsName": "MG_Video",
                "vendorId": 38,
                "gameCode": "4e58131adb95bb061a40e6e309116c19",
                "state": 1,
                "vendorImg": "https:\/\/ossimg.jalwa-jalwa.com\/Jalwa\/vendorlogo\/vendorlogo_202503111054516cx3.png"
            },
            {
                "slotsTypeID": 27,
                "slotsName": "Motivation",
                "vendorId": 27,
                "gameCode": "709f3ade034d0eb105e087a0f8bebc09",
                "state": 1,
                "vendorImg": "https:\/\/91lotteryxbet.site\/felobet\/game-icon\/motivation-gaming.png"
            },
            {
                "slotsTypeID": 27,
                "slotsName": "Motivation",
                "vendorId": 27,
                "gameCode": "709f3ade034d0eb105e087a0f8bebc09",
                "state": 1,
                "vendorImg": "https:\/\/huidu-bucket.s3.ap-southeast-1.amazonaws.com\/api\/yeebet\/Immortal-Roulette-ROU06.png"
            },
            {
                "slotsTypeID": 26,
                "slotsName": "AG_Gaming",
                "vendorId": 26,
                "gameCode": "d0e052b031dfcdb08d1803f4bcc618ef",
                "state": 1,
                "vendorImg": "https:\/\/91lotteryxbet.site\/felobet\/game-icon\/api\/ezugi\/ezugi.png"
            },
            {
                "slotsTypeID": 26,
                "slotsName": "AG_Gaming",
                "vendorId": 26,
                "gameCode": "067a093c1258d306d8d4659713c30985",
                "state": 1,
                "vendorImg": "https:\/\/huidu-bucket.s3.ap-southeast-1.amazonaws.com\/api\/xatsrgaming\/Astarlogo.png"
            },
            {
                "slotsTypeID": 27,
                "slotsName": "SA gaming",
                "vendorId": 27,
                "gameCode": "1e7f104c0b7235f481337d0d9506ee18",
                "state": 1,
                "vendorImg": "https:\/\/91lotteryxbet.site\/felobet\/game-icon\/api\/sagaming\/sagaming.png"
            },
            {
                "slotsTypeID": 27,
                "slotsName": "Yeebet Lobby",
                "vendorId": 27,
                "gameCode": "c14c2eb3f2f0977c20620e1345ff2958",
                "state": 1,
                "vendorImg": "https:\/\/tse1.mm.bing.net\/th\/id\/OIP.n9K_AWN7rXKE8VdwnNgZcQAAAA?cb=12&rs=1&pid=ImgDetMain&o=7&rm=3"
            }
        ],
        "slot": [
           {
    "slotsTypeID": 222,
    "slotsName": "Hot Game",
    "vendorId": 222,
    "vendorCode": "Hot Game",
    "state": 1,
    "vendorImg": "https://placehold.co/330x440/00c2ff/000?text=Hot+Game"
},

             {
        "slotsTypeID": 12,
        "slotsName": "JILI",
        "vendorId": 12,
        "vendorCode": "JILI",
        "state": 1,
        "vendorImg": "https://placehold.co/330x440/00c2ff/000?text=JILI"
    },
    {
        "slotsTypeID": 11,
        "slotsName": "JDB",
        "vendorId": 11,
        "vendorCode": "JDB",
        "state": 1,
        "vendorImg": "https://placehold.co/330x440/00c2ff/000?text=JDB"
    },
    {
        "slotsTypeID": 14,
        "slotsName": "PG",
        "vendorId": 14,
        "vendorCode": "PG",
        "state": 1,
        "vendorImg": "https://placehold.co/330x440/00c2ff/000?text=PG"
    },
     {
        "slotsTypeID": 10,
        "slotsName": "INOUT",
        "vendorId": 10,
        "vendorCode": "INOUT",
        "state": 1,
        "vendorImg": "https://placehold.co/330x440/00c2ff/000?text=INOUT"
    },
        
        
        {
    "slotsTypeID": 22,
    "slotsName": "BNG",
    "vendorId": 22,
    "vendorCode": "BNG",
    "state": 1,
    "vendorImg": "https://placehold.co/330x440/00c2ff/000?text=BNG"
},

        {
        "slotsTypeID": 2,
        "slotsName": "5G",
        "vendorId": 2,
        "vendorCode": "5G",
        "state": 1,
        "vendorImg": "https://placehold.co/330x440/00c2ff/000?text=5G"
    },
     {
        "slotsTypeID": 37,
        "slotsName": "MG",
        "vendorId": 37,
        "vendorCode": "MG",
        "state": 1,
        "vendorImg": "https://placehold.co/330x440/00c2ff/000?text=MG"
    },
    {
        "slotsTypeID": 3,
        "slotsName": "BTGAMING",
        "vendorId": 3,
        "vendorCode": "BTGAMING",
        "state": 1,
        "vendorImg": "https://placehold.co/330x440/00c2ff/000?text=BTGAMING"
    },
    {
        "slotsTypeID": 4,
        "slotsName": "CQ9",
        "vendorId": 4,
        "vendorCode": "CQ9",
        "state": 1,
        "vendorImg": "https://placehold.co/330x440/00c2ff/000?text=CQ9"
    },
    {
        "slotsTypeID": 5,
        "slotsName": "EXPANSE",
        "vendorId": 5,
        "vendorCode": "EXPANSE",
        "state": 1,
        "vendorImg": "https://placehold.co/330x440/00c2ff/000?text=EXPANSE"
    },
    {
        "slotsTypeID": 6,
        "slotsName": "FACHAIGAMING",
        "vendorId": 6,
        "vendorCode": "FACHAIGAMING",
        "state": 1,
        "vendorImg": "https://placehold.co/330x440/00c2ff/000?text=FACHAIGAMING"
    },
    {
        "slotsTypeID": 7,
        "slotsName": "FASTSPIN",
        "vendorId": 7,
        "vendorCode": "FASTSPIN",
        "state": 1,
        "vendorImg": "https://placehold.co/330x440/00c2ff/000?text=FASTSPIN"
    },
    {
        "slotsTypeID": 8,
        "slotsName": "GALAXSYS",
        "vendorId": 8,
        "vendorCode": "GALAXSYS",
        "state": 1,
        "vendorImg": "https://placehold.co/330x440/00c2ff/000?text=GALAXSYS"
    },
   
    
   
    {
        "slotsTypeID": 13,
        "slotsName": "MINI",
        "vendorId": 13,
        "vendorCode": "MINI",
        "state": 1,
        "vendorImg": "https://placehold.co/330x440/00c2ff/000?text=MINI"
    },
    
    {
        "slotsTypeID": 15,
        "slotsName": "RG",
        "vendorId": 15,
        "vendorCode": "RG",
        "state": 1,
        "vendorImg": "https://placehold.co/330x440/00c2ff/000?text=RG"
    },
   
    {
        "slotsTypeID": 17,
        "slotsName": "SPRIBE",
        "vendorId": 17,
        "vendorCode": "SPRIBE",
        "state": 1,
        "vendorImg": "https://placehold.co/330x440/00c2ff/000?text=SPRIBE"
    },
   
    {
        "slotsTypeID": 20,
        "slotsName": "V8Card",
        "vendorId": 20,
        "vendorCode": "V8Card",
        "state": 1,
        "vendorImg": "https://placehold.co/330x440/00c2ff/000?text=V8"
    },
    {
        "slotsTypeID": 21,
        "slotsName": "WONWON",
        "vendorId": 21,
        "vendorCode": "WONWON",
        "state": 1,
        "vendorImg": "https://placehold.co/330x440/00c2ff/000?text=WONWON"
    }
        ],
        "chess": [
 {
                "slotsTypeID": 19,
                "slotsName": "Card365",
                "vendorId": 19,
                "vendorCode": "Card365",
                "state": 1,
                "vendorImg": "https://ossimg.tashanedc.com/Tashanwin/vendorlogo/vendorlogo_20250329173756qxy5.png"
            },
            {
                "slotsTypeID": 21,
                "slotsName": "V8Card",
                "vendorId": 21,
                "vendorCode": "V8Card",
                "state": 1,
                "vendorImg": "https://ossimg.tashanedc.com/Tashanwin/vendorlogo/vendorlogo_20250329173808g9cj.png"
            }
        ],
        "fish": [
            {
                "gameID": "e794bf5717aca371152df192341fe68b",
                "gameNameEn": "Royal Fishing",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/1.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "9ec2a18752f83e45ccedde8dfeb0f6a7",
                "gameNameEn": "All-star Fishing",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/119.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "e333695bcff28acdbecc641ae6ee2b23",
                "gameNameEn": "Bombing Fishing",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/20.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "bbae6016f79f3df74e453eda164c08a4",
                "gameNameEn": "Dinosaur Tycoon II",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/212.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "3cf4a85cb6dcf4d8836c982c359cd72d",
                "gameNameEn": "Jack Pot Fishing",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/32.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "eef3e28f0e3e7b72cbca61e7924d00f1",
                "gameNameEn": "Dinosaur Tycoon",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/42.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "1200b82493e4788d038849bca884d773",
                "gameNameEn": "Dragon Fortune",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/60.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "f02ede19c5953fce22c6098d860dadf4",
                "gameNameEn": "Boom Legend",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/71.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "caacafe3f64a6279e10a378ede09ff38",
                "gameNameEn": "Mega Fishing",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/74.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "71c68a4ddb63bdc8488114a08e603f1c",
                "gameNameEn": "Happy Fishing",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/82.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
             
                "gameNameEn": "WD FuWa Fishing",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Fish/SFG_WDFuWaFishing.png",
                "vendorId": 37,
                "vendorCode": "MG_Fish",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                
                "gameNameEn": "WD Gold Blast Fishing",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Fish/SFG_WDGoldBlastFishing.png",
                "vendorId": 37,
                "vendorCode": "MG_Fish",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
               
                "gameNameEn": "WD Golden Fortune Fishing",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Fish/SFG_WDGoldenFortuneFishing.png",
                "vendorId": 37,
                "vendorCode": "MG_Fish",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
               
                "gameNameEn": "WD Golden Fuwa Fishing",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Fish/SFG_WDGoldenFuwaFishing.png",
                "vendorId": 37,
                "vendorCode": "MG_Fish",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
             
                "gameNameEn": "WD Golden Tyrant Fishing",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Fish/SFG_WDGoldenTyrantFishing.png",
                "vendorId": 37,
                "vendorCode": "MG_Fish",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                
                "gameNameEn": "WD Merry Island Fishing",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Fish/SFG_WDMerryIslandFishing.png",
                "vendorId": 37,
                "vendorCode": "MG_Fish",
                "imgUrl2": "",
                "customGameType": 0
            }
        ],
        "flash": [
            {
                "gameID": "a04d1f3eb8ccec8a4823bdf18e3f0e84",
                "gameNameEn": "Aviator",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/SPRIBE/aviator_20250210120506414.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/800_20250210113151154.png",
                "customGameType": 0
            },
           
            {
                "gameID": "5c4a12fb0a9b296d9b0d5f9e1cd41d65",
                "gameNameEn": "Mines",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/100.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/100_20250210102610370.png",
                "customGameType": 0
            },
           
            {
                "gameID": "eabf08253165b6bb2646e403de625d1a",
                "gameNameEn": "Limbo",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/110_20250210111637635.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/110_20250210102531478.png",
                "customGameType": 0
            },
           
            {
                "gameID": "c68a515f0b3b10eec96cf6d33299f4e2",
                "gameNameEn": "Goal",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/105_20250210113618898.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/105_20250210113618945.png",
                "customGameType": 0
            },
            {
                "gameID": "8a87aae7a3624d284306e9c6fe1b3e9c",
                "gameNameEn": "Dice",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/102_20250210111705541.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/102_20250210102559317.png",
                "customGameType": 0
            },
            {
                "gameID": "a04d1f3eb8ccec8a4823bdf18e3f0e84",
                "gameNameEn": "Aviator-1Min",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/801_20250210111620851.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/801_20250210102517568.png",
                "customGameType": 0
            },
            {
                "gameID": "6ab7a4fe5161936012d6b06143918223",
                "gameNameEn": "Plinko",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/103_20250210111953841.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/103_20250210102957755.png",
                "customGameType": 0
            },
            {
                "gameID": "a669c993b0e1f1b7da100fcf95516bdf",
                "gameNameEn": "Hilo",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/101_20250210111652965.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/101_20250210102547898.png",
                "customGameType": 0
            },
           
            {
                "gameID": "b31720b3cd65d917a1a96ef61a72b672",
                "gameNameEn": "Hotline",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/107.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/107_20250210102621503.png",
                "customGameType": 0
            },
           
            {
                "gameID": "c68a515f0b3b10eec96cf6d33299f4e2",
                "gameNameEn": "Goal Wave",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/502_20250210111723255.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/502_20250210102634845.png",
                "customGameType": 0
            },
            {
                "gameID": "9dc7ac6155c5a19c1cc204853e426367",
                "gameNameEn": "Mini Roulette",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/104_20250210111939890.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/104_20250210102944301.png",
                "customGameType": 0
            },
            
            {
                "gameID": "c311eb4bbba03b105d150504931f2479",
                "gameNameEn": "Keno80",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/900_20250210112011401.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/900_20250210103014351.png",
                "customGameType": 0
            },
            {
                "gameID": "c311eb4bbba03b105d150504931f2479",
                "gameNameEn": "Keno",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/106.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/106_20250210102847401.png",
                "customGameType": 0
            },
            
            {
                "gameID": "a9f60e017f2765c74e1ec80473ac4ffa",
                "gameNameEn": "Triple",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/112_20250210111856804.png",
                "vendorId": 23,
                "vendorCode": "TB_Chess",
                "imgUrl2": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/112_20250210102805495.png",
                "customGameType": 0
            },
           
            
            
            {
                "gameID": "72ce7e04ce95ee94eef172c0dfd6dc17",
                "gameNameEn": "Go Rush",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/224_20250213130849608.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "5c4a12fb0a9b296d9b0d5f9e1cd41d65",
                "gameNameEn": "Mines",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/229_20250213130902179.png",
                "vendorId": 18,
                "vendorCode": "JILI",
                "imgUrl2": "",
                "customGameType": 0
            }
            
        ],
        "lottery": [
            {
                "id": 1,
                "categoryCode": "Win Go",
                "categoryName": "WinGo彩票",
                "state": 1,
                "sort": 10,
                "categoryImg": "https://ossimg.tashanedc.com/Tashanwin/lotterycategory/lotterycategory_20250412120719dqfv.png",
                "wingoAmount": null,
                "k3Amount": null,
                "fiveDAmount": null,
                "trxWingoAmount": null
            },
            {
                "id": 2,
                "categoryCode": "K3",
                "categoryName": "K3彩票",
                "state": 1,
                "sort": 8,
                "categoryImg": "https://ossimg.tashanedc.com/Tashanwin/lotterycategory/lotterycategory_2025041212074073ug.png",
                "wingoAmount": null,
                "k3Amount": null,
                "fiveDAmount": null,
                "trxWingoAmount": null
            },
            {
                "id": 3,
                "categoryCode": "5D",
                "categoryName": "5D彩票",
                "state": 1,
                "sort": 1,
                "categoryImg": "https://ossimg.tashanedc.com/Tashanwin/lotterycategory/lotterycategory_2025041212080195lo.png",
                "wingoAmount": null,
                "k3Amount": null,
                "fiveDAmount": null,
                "trxWingoAmount": null
            }
          
        ],
        "awardRecordList": [
            {
                "orderId": 9718293,
                "userId": 12605270,
                "userPhoto": "1",
                "userName": "918853451860",
                "gameName": "Money Coming",
                "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/51.png",
                "imgUrl2": "",
                "multiple": 20.00,
                "bonusAmount": 100.00,
                "multipleName": "20-29",
                "createTime": "2025-02-18 13:39:01"
            },
            {
                "orderId": 9718292,
                "userId": 1276302,
                "userPhoto": "1",
                "userName": "919401169794",
                "gameName": "Money Coming",
                "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/51.png",
                "imgUrl2": "",
                "multiple": 50.00,
                "bonusAmount": 300.00,
                "multipleName": "40-59",
                "createTime": "2025-02-18 13:39:01"
            },
            {
                "orderId": 9718291,
                "userId": 169929,
                "userPhoto": "4",
                "userName": "919813473272",
                "gameName": "Fortune Gems",
                "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/109.png",
                "imgUrl2": "",
                "multiple": 10.67,
                "bonusAmount": 50.00,
                "multipleName": "10-19",
                "createTime": "2025-02-18 13:39:01"
            },
            {
                "orderId": 9718290,
                "userId": 5160891,
                "userPhoto": "5",
                "userName": "917503916092",
                "gameName": "Money Coming",
                "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/51.png",
                "imgUrl2": "",
                "multiple": 41.00,
                "bonusAmount": 300.00,
                "multipleName": "40-59",
                "createTime": "2025-02-18 13:39:01"
            },
            {
                "orderId": 9718289,
                "userId": 3864680,
                "userPhoto": "1",
                "userName": "916351724933",
                "gameName": "Money Coming",
                "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/51.png",
                "imgUrl2": "",
                "multiple": 30.00,
                "bonusAmount": 200.00,
                "multipleName": "30-39",
                "createTime": "2025-02-18 13:39:01"
            },
            {
                "orderId": 9718288,
                "userId": 12985588,
                "userPhoto": "1",
                "userName": "918088072814",
                "gameName": "Money Coming",
                "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/51.png",
                "imgUrl2": "",
                "multiple": 10.00,
                "bonusAmount": 50.00,
                "multipleName": "10-19",
                "createTime": "2025-02-18 13:39:01"
            },
            {
                "orderId": 9718287,
                "userId": 9552754,
                "userPhoto": "7",
                "userName": "919429848853",
                "gameName": "Crazy777",
                "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/35.png",
                "imgUrl2": "",
                "multiple": 36.67,
                "bonusAmount": 200.00,
                "multipleName": "30-39",
                "createTime": "2025-02-18 13:39:01"
            },
            {
                "orderId": 9718286,
                "userId": 9841016,
                "userPhoto": "6",
                "userName": "918690536005",
                "gameName": "Royal Fishing",
                "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/1.png",
                "imgUrl2": "",
                "multiple": 11.67,
                "bonusAmount": 50.00,
                "multipleName": "10-19",
                "createTime": "2025-02-18 13:39:01"
            },
            {
                "orderId": 9718285,
                "userId": 13598097,
                "userPhoto": "1",
                "userName": "918953816512",
                "gameName": "Fortune Gems",
                "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/109.png",
                "imgUrl2": "",
                "multiple": 26.67,
                "bonusAmount": 100.00,
                "multipleName": "20-29",
                "createTime": "2025-02-18 13:39:01"
            },
            {
                "orderId": 9718284,
                "userId": 12605270,
                "userPhoto": "1",
                "userName": "918853451860",
                "gameName": "Money Coming",
                "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/51.png",
                "imgUrl2": "",
                "multiple": 11.00,
                "bonusAmount": 50.00,
                "multipleName": "10-19",
                "createTime": "2025-02-18 13:39:01"
            }
        ]
    },
    "code": 0,
    "msg": "Succeed",
    "msgCode": 0,
    "serviceNowTime": "' . $serviceNowTimeFormatted . '"
}';

$data = json_decode($jsonData, true);

$response = json_encode($data, JSON_PRETTY_PRINT);

header('Content-Type: application/json');
echo $response;

?>
