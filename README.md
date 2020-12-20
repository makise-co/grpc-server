# grpc-server
Makise-Co GRPC server

## Requirements
* PHP >= 7.4
* `ext-protobuf` - PHP C extension (most performant solution)
* or `google/protobuf`- PHP User level implementation

## Example
* Example located in [example](example) directory.
* Run `make gen-proto`, it will generate needed PHP classes
* Run `php test.php` (the server will listen on address - 127.0.0.1:9090)
* Enjoy it

Benchmark: 

* CPU: Intel Core i7-9750H
* OS: WSL 2 (Ubuntu)
* PHP: 7.4 (7.4.12) with OPCache enabled
* Swoole: v4.5.9
* Number of Swoole Workers: 2 (2 threads)

Bench command: `ghz --insecure --proto example/api/example/users.proto --call 'api.Users.Get'
    -d '{"id": 228}' -c100 -n1000 127.0.0.1:9090`

<details>
<summary>Results (concurrency level: 100):</summary>

```
Summary:
Count:        1000
Total:        42.61 ms
Slowest:      5.57 ms
Fastest:      2.16 ms
Average:      3.58 ms
Requests/sec: 23466.69

Response time histogram:
2.156 [1]     |
2.497 [16]    |∎∎∎
2.839 [146]   |∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎
3.180 [139]   |∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎
3.522 [158]   |∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎
3.863 [215]   |∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎
4.205 [163]   |∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎
4.546 [77]    |∎∎∎∎∎∎∎∎∎∎∎∎∎∎
4.888 [49]    |∎∎∎∎∎∎∎∎∎
5.229 [20]    |∎∎∎∎
5.571 [16]    |∎∎∎

Latency distribution:
10 % in 2.70 ms
25 % in 3.06 ms
50 % in 3.59 ms
75 % in 4.00 ms
90 % in 4.48 ms
95 % in 4.66 ms
99 % in 5.32 ms

Status code distribution:
[OK]   1000 responses
```

</details>

<details>
<summary>Results (concurrency level: 250):</summary>

```
Summary:
  Count:        10000
  Total:        359.61 ms
  Slowest:      52.35 ms
  Fastest:      0.55 ms
  Average:      8.66 ms
  Requests/sec: 27808.01

Response time histogram:
  0.551 [1]     |
  5.731 [2930]  |∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎
  10.910 [4723] |∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎
  16.090 [1638] |∎∎∎∎∎∎∎∎∎∎∎∎∎∎
  21.270 [501]  |∎∎∎∎
  26.450 [146]  |∎
  31.629 [41]   |
  36.809 [11]   |
  41.989 [6]    |
  47.169 [1]    |
  52.349 [2]    |

Latency distribution:
  10 % in 4.00 ms
  25 % in 5.10 ms
  50 % in 7.65 ms
  75 % in 10.62 ms
  90 % in 14.59 ms
  95 % in 17.37 ms
  99 % in 24.76 ms

Status code distribution:
  [OK]   10000 responses
```

</details>

<details>
<summary>Results (concurrency level: 500):</summary>

```
Summary:
  Count:        10000
  Total:        355.55 ms
  Slowest:      159.87 ms
  Fastest:      0.55 ms
  Average:      16.99 ms
  Requests/sec: 28125.46

Response time histogram:
  0.553 [1]     |
  16.484 [6441] |∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎
  32.416 [2488] |∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎
  48.347 [735]  |∎∎∎∎∎
  64.278 [256]  |∎∎
  80.209 [53]   |
  96.141 [18]   |
  112.072 [5]   |
  128.003 [1]   |
  143.934 [1]   |
  159.866 [1]   |

Latency distribution:
  10 % in 4.03 ms
  25 % in 5.68 ms
  50 % in 13.68 ms
  75 % in 22.57 ms
  90 % in 33.12 ms
  95 % in 42.20 ms
  99 % in 62.16 ms

Status code distribution:
  [OK]   10000 responses
```

</details>


Bench command: `ghz --insecure --proto example/api/example/users.proto --call 'api.Users.List'
-c100 -n1000 127.0.0.1:9090`

<details>
<summary>Results (concurrency level: 100):</summary>

```
Summary:
  Count:        1000
  Total:        87.91 ms
  Slowest:      10.37 ms
  Fastest:      4.18 ms
  Average:      7.99 ms
  Requests/sec: 11374.92

Response time histogram:
  4.185 [1]     |
  4.803 [33]    |∎∎∎∎∎∎∎
  5.421 [32]    |∎∎∎∎∎∎∎
  6.040 [28]    |∎∎∎∎∎∎
  6.658 [48]    |∎∎∎∎∎∎∎∎∎∎
  7.277 [148]   |∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎
  7.895 [152]   |∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎
  8.513 [141]   |∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎
  9.132 [176]   |∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎
  9.750 [186]   |∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎
  10.368 [55]   |∎∎∎∎∎∎∎∎∎∎∎∎

Latency distribution:
  10 % in 6.20 ms
  25 % in 7.12 ms
  50 % in 8.18 ms
  75 % in 9.11 ms
  90 % in 9.60 ms
  95 % in 9.78 ms
  99 % in 10.09 ms

Status code distribution:
  [OK]   1000 responses
```

</details>

<details>
<summary>Results (concurrency level: 250):</summary>

```
Summary:
  Count:        10000
  Total:        750.43 ms
  Slowest:      81.14 ms
  Fastest:      3.85 ms
  Average:      18.41 ms
  Requests/sec: 13325.76

Response time histogram:
  3.850 [1]     |
  11.579 [2810] |∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎
  19.308 [4013] |∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎
  27.038 [1859] |∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎
  34.767 [796]  |∎∎∎∎∎∎∎∎
  42.496 [310]  |∎∎∎
  50.225 [132]  |∎
  57.954 [41]   |
  65.683 [23]   |
  73.412 [12]   |
  81.141 [3]    |

Latency distribution:
  10 % in 9.26 ms
  25 % in 10.29 ms
  50 % in 16.72 ms
  75 % in 23.26 ms
  90 % in 30.32 ms
  95 % in 35.79 ms
  99 % in 48.22 ms

Status code distribution:
  [OK]   10000 responses
```

</details>

<details>
<summary>Results (concurrency level: 500):</summary>

```
Summary:
  Count:        10000
  Total:        757.74 ms
  Slowest:      287.87 ms
  Fastest:      6.96 ms
  Average:      36.62 ms
  Requests/sec: 13197.18

Response time histogram:
  6.964 [1]     |
  35.055 [6711] |∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎∎
  63.146 [2047] |∎∎∎∎∎∎∎∎∎∎∎∎
  91.237 [785]  |∎∎∎∎∎
  119.328 [312] |∎∎
  147.419 [92]  |∎
  175.510 [35]  |
  203.601 [12]  |
  231.692 [2]   |
  259.783 [1]   |
  287.874 [2]   |

Latency distribution:
  10 % in 9.19 ms
  25 % in 11.03 ms
  50 % in 31.66 ms
  75 % in 50.98 ms
  90 % in 72.58 ms
  95 % in 89.31 ms
  99 % in 128.17 ms

Status code distribution:
  [OK]   10000 responses
```

</details>
