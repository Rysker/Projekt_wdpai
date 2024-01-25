--
-- PostgreSQL database dump
--

-- Dumped from database version 16.1 (Debian 16.1-1.pgdg120+1)
-- Dumped by pg_dump version 16.1

-- Started on 2024-01-25 02:38:30 UTC

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 4 (class 2615 OID 2200)
-- Name: public; Type: SCHEMA; Schema: -; Owner: pg_database_owner
--

CREATE SCHEMA public;


ALTER SCHEMA public OWNER TO pg_database_owner;

--
-- TOC entry 3534 (class 0 OID 0)
-- Dependencies: 4
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: pg_database_owner
--

COMMENT ON SCHEMA public IS 'standard public schema';


--
-- TOC entry 260 (class 1255 OID 25353)
-- Name: add_user(character varying, character varying); Type: PROCEDURE; Schema: public; Owner: docker
--

CREATE PROCEDURE public.add_user(IN p_email character varying, IN p_password character varying)
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_user_id INT;
BEGIN
    BEGIN
        -- Create the user
        INSERT INTO public.user (email, password, id_status, id_currency)
        VALUES (p_email, p_password, 1, 1)
        RETURNING id_user INTO v_user_id;

        -- Give user role 'User'
        INSERT INTO public.usersroles (id_user, id_role) VALUES (v_user_id, 1);

        COMMIT;
    END;
END;
$$;


ALTER PROCEDURE public.add_user(IN p_email character varying, IN p_password character varying) OWNER TO docker;

--
-- TOC entry 261 (class 1255 OID 17046)
-- Name: calculate_open_positions(integer); Type: FUNCTION; Schema: public; Owner: docker
--

CREATE FUNCTION public.calculate_open_positions(investment_id integer) RETURNS TABLE(id_transaction integer, transaction_type character varying, quantity integer, price numeric, date date, open_position integer)
    LANGUAGE plpgsql
    AS $$DECLARE
    total_quantity INT := 0;
BEGIN
    RETURN QUERY
    SELECT
        t.id_transaction,
        tt.transaction_type,  -- Assuming 'transaction_type' is the correct column name in 'public.transaction_type'
        t.quantity,
        t.price,
        t.date,
        CASE WHEN tt.transaction_type = 'BUY' THEN
            total_quantity + t.quantity
        WHEN tt.transaction_type = 'SELL' THEN
            total_quantity - t.quantity
        END AS open_position
    FROM
        public.transaction t
        NATURAL JOIN public.investment
        NATURAL JOIN public.transaction_type tt
    WHERE
        public.investment.id_investment = investment_id
    ORDER BY
        t.date DESC;

END;$$;


ALTER FUNCTION public.calculate_open_positions(investment_id integer) OWNER TO docker;

--
-- TOC entry 264 (class 1255 OID 25364)
-- Name: checkquantity(integer, integer); Type: FUNCTION; Schema: public; Owner: docker
--

CREATE FUNCTION public.checkquantity(portfolio_id_input integer, investment_id_input integer) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
DECLARE
    total_quantity DECIMAL(18, 2);
BEGIN
    SELECT SUM(CASE WHEN tt.transaction_type = 'SELL' THEN -t.quantity ELSE t.quantity END)
    INTO total_quantity
    FROM public.investment i
    NATURAL JOIN public.transaction t
    NATURAL JOIN public.user u
    NATURAL JOIN public.transaction_type tt
    NATURAL JOIN public.portfolio p
    WHERE p.id_portfolio = portfolio_id_input AND i.id_investment = investment_id_input;

    RETURN total_quantity;
END;
$$;


ALTER FUNCTION public.checkquantity(portfolio_id_input integer, investment_id_input integer) OWNER TO docker;

--
-- TOC entry 259 (class 1255 OID 25350)
-- Name: getforex(character varying); Type: FUNCTION; Schema: public; Owner: docker
--

CREATE FUNCTION public.getforex(to_currency_ticker character varying) RETURNS TABLE(from_currency_code character varying, to_currency_code character varying, price numeric)
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN QUERY
    SELECT
        c1.currency_code AS currency1,
        c2.currency_code AS currency2,
        f.price as price
    FROM
        public.forex f
    JOIN
        public.currency c1 ON f.id_from = c1.id_currency
    JOIN
        public.currency c2 ON f.id_to = c2.id_currency
    WHERE
        c2.currency_code = to_currency_ticker;
END;
$$;


ALTER FUNCTION public.getforex(to_currency_ticker character varying) OWNER TO docker;

--
-- TOC entry 263 (class 1255 OID 25320)
-- Name: handle_transaction(integer, integer, integer, integer, numeric, date); Type: PROCEDURE; Schema: public; Owner: docker
--

CREATE PROCEDURE public.handle_transaction(IN portfolio_id integer, IN id_asset_input integer, IN id_type_input integer, IN quantity_input integer, IN price_input numeric, IN date_input date)
    LANGUAGE plpgsql
    AS $$DECLARE
    investment_id INTEGER;
BEGIN
    BEGIN
        IF hasInvested(portfolio_id, id_asset_input) THEN
            SELECT id_investment INTO investment_id
            FROM public.investment
            NATURAL JOIN public.portfolio
            NATURAL JOIN public.asset
            WHERE id_portfolio = portfolio_id AND id_asset = id_asset_input;
        ELSE
            INSERT INTO public.investment (id_portfolio, id_asset)
            VALUES (portfolio_id, id_asset_input)
            RETURNING id_investment INTO investment_id;
        END IF;

        INSERT INTO public.transaction (id_transaction_type, quantity, price, date, id_investment)
        VALUES (id_type_input, quantity_input, price_input, date_input, investment_id);

        IF checkquantity(portfolio_id, investment_id) < 0 THEN
            ROLLBACK;
            RAISE EXCEPTION 'Quantity cannot be negative after insertion. Transaction rolled back.';
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END;
    COMMIT;
END;$$;


ALTER PROCEDURE public.handle_transaction(IN portfolio_id integer, IN id_asset_input integer, IN id_type_input integer, IN quantity_input integer, IN price_input numeric, IN date_input date) OWNER TO docker;

--
-- TOC entry 262 (class 1255 OID 17073)
-- Name: hasinvested(integer, integer); Type: FUNCTION; Schema: public; Owner: docker
--

CREATE FUNCTION public.hasinvested(id_user_input integer, id_asset_input integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN EXISTS (
        SELECT 1
        FROM public.investment
		NATURAL JOIN public.portfolio
		NATURAL JOIN public.asset
        WHERE id_portfolio = id_user_input AND id_asset = id_asset_input
    );
END;
$$;


ALTER FUNCTION public.hasinvested(id_user_input integer, id_asset_input integer) OWNER TO docker;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 226 (class 1259 OID 16925)
-- Name: asset; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.asset (
    id_asset integer NOT NULL,
    id_market integer NOT NULL,
    id_asset_type integer NOT NULL,
    asset_name character varying,
    asset_ticker character varying,
    id_currency integer
);


ALTER TABLE public.asset OWNER TO docker;

--
-- TOC entry 225 (class 1259 OID 16924)
-- Name: asset_id_asset_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

ALTER TABLE public.asset ALTER COLUMN id_asset ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.asset_id_asset_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- TOC entry 224 (class 1259 OID 16914)
-- Name: asset_type; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.asset_type (
    id_asset_type integer NOT NULL,
    type_name character varying
);


ALTER TABLE public.asset_type OWNER TO docker;

--
-- TOC entry 223 (class 1259 OID 16913)
-- Name: asset_type_id_asset_type_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

ALTER TABLE public.asset_type ALTER COLUMN id_asset_type ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.asset_type_id_asset_type_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- TOC entry 228 (class 1259 OID 16943)
-- Name: investment; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.investment (
    id_investment integer NOT NULL,
    id_portfolio integer NOT NULL,
    id_asset integer NOT NULL
);


ALTER TABLE public.investment OWNER TO docker;

--
-- TOC entry 218 (class 1259 OID 16880)
-- Name: portfolio; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.portfolio (
    id_portfolio integer NOT NULL,
    id_user integer NOT NULL,
    name character varying NOT NULL
);


ALTER TABLE public.portfolio OWNER TO docker;

--
-- TOC entry 216 (class 1259 OID 16872)
-- Name: user; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public."user" (
    id_user integer NOT NULL,
    email character varying NOT NULL,
    password character varying(255) NOT NULL,
    token character varying(255),
    id_status integer,
    id_currency integer
);


ALTER TABLE public."user" OWNER TO docker;

--
-- TOC entry 240 (class 1259 OID 25292)
-- Name: assets_popularity; Type: VIEW; Schema: public; Owner: docker
--

CREATE VIEW public.assets_popularity AS
 SELECT asset.id_asset,
    count(asset.id_asset) AS popularity
   FROM (((public.asset
     CROSS JOIN public."user")
     JOIN public.portfolio USING (id_user))
     JOIN public.investment USING (id_asset, id_portfolio))
  GROUP BY asset.id_asset;


ALTER VIEW public.assets_popularity OWNER TO docker;

--
-- TOC entry 220 (class 1259 OID 16893)
-- Name: country; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.country (
    id_country integer NOT NULL,
    country_name character varying NOT NULL
);


ALTER TABLE public.country OWNER TO docker;

--
-- TOC entry 219 (class 1259 OID 16892)
-- Name: country_id_country_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

ALTER TABLE public.country ALTER COLUMN id_country ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.country_id_country_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- TOC entry 235 (class 1259 OID 17027)
-- Name: currency; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.currency (
    id_currency integer NOT NULL,
    currency_name character varying,
    currency_code character varying,
    currency_sign character varying
);


ALTER TABLE public.currency OWNER TO docker;

--
-- TOC entry 234 (class 1259 OID 17026)
-- Name: currency_id_currency_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

ALTER TABLE public.currency ALTER COLUMN id_currency ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.currency_id_currency_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- TOC entry 247 (class 1259 OID 33557)
-- Name: current_price; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.current_price (
    id_asset integer NOT NULL,
    price numeric NOT NULL,
    modification timestamp with time zone
);


ALTER TABLE public.current_price OWNER TO docker;

--
-- TOC entry 244 (class 1259 OID 25331)
-- Name: forex; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.forex (
    id_from integer NOT NULL,
    id_to integer,
    price numeric,
    modification timestamp with time zone
);


ALTER TABLE public.forex OWNER TO docker;

--
-- TOC entry 227 (class 1259 OID 16942)
-- Name: investment_id_investment_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

ALTER TABLE public.investment ALTER COLUMN id_investment ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.investment_id_investment_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- TOC entry 222 (class 1259 OID 16901)
-- Name: market; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.market (
    id_market integer NOT NULL,
    id_country integer NOT NULL,
    market_name character varying,
    market_ticker character varying
);


ALTER TABLE public.market OWNER TO docker;

--
-- TOC entry 221 (class 1259 OID 16900)
-- Name: market_id_market_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

ALTER TABLE public.market ALTER COLUMN id_market ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.market_id_market_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- TOC entry 217 (class 1259 OID 16879)
-- Name: portfolio_id_portfolio_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

ALTER TABLE public.portfolio ALTER COLUMN id_portfolio ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.portfolio_id_portfolio_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- TOC entry 237 (class 1259 OID 25266)
-- Name: role; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.role (
    id_role integer NOT NULL,
    role_name character varying
);


ALTER TABLE public.role OWNER TO docker;

--
-- TOC entry 236 (class 1259 OID 25265)
-- Name: role_id_role_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

ALTER TABLE public.role ALTER COLUMN id_role ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.role_id_role_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- TOC entry 246 (class 1259 OID 25366)
-- Name: sessions; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.sessions (
    id_session integer NOT NULL,
    id_user integer,
    session_token character varying,
    "time" time without time zone
);


ALTER TABLE public.sessions OWNER TO docker;

--
-- TOC entry 245 (class 1259 OID 25365)
-- Name: sessions_id_session_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

ALTER TABLE public.sessions ALTER COLUMN id_session ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.sessions_id_session_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
    CYCLE
);


--
-- TOC entry 242 (class 1259 OID 25298)
-- Name: status; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.status (
    id_status integer NOT NULL,
    status_name character varying
);


ALTER TABLE public.status OWNER TO docker;

--
-- TOC entry 241 (class 1259 OID 25297)
-- Name: status_id_status_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

ALTER TABLE public.status ALTER COLUMN id_status ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.status_id_status_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- TOC entry 231 (class 1259 OID 16966)
-- Name: transaction; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.transaction (
    id_transaction integer NOT NULL,
    id_transaction_type integer NOT NULL,
    quantity integer NOT NULL,
    price numeric NOT NULL,
    date date NOT NULL,
    id_investment integer NOT NULL
);


ALTER TABLE public.transaction OWNER TO docker;

--
-- TOC entry 230 (class 1259 OID 16965)
-- Name: transaction_id_transaction_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

ALTER TABLE public.transaction ALTER COLUMN id_transaction ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.transaction_id_transaction_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- TOC entry 229 (class 1259 OID 16958)
-- Name: transaction_type; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.transaction_type (
    id_transaction_type integer NOT NULL,
    transaction_type character varying NOT NULL
);


ALTER TABLE public.transaction_type OWNER TO docker;

--
-- TOC entry 233 (class 1259 OID 17006)
-- Name: transaction_type_id_transaction_type_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

ALTER TABLE public.transaction_type ALTER COLUMN id_transaction_type ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.transaction_type_id_transaction_type_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- TOC entry 215 (class 1259 OID 16871)
-- Name: user_id_user_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

ALTER TABLE public."user" ALTER COLUMN id_user ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.user_id_user_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- TOC entry 238 (class 1259 OID 25273)
-- Name: usersroles; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.usersroles (
    id_user integer NOT NULL,
    id_role integer NOT NULL
);


ALTER TABLE public.usersroles OWNER TO docker;

--
-- TOC entry 239 (class 1259 OID 25288)
-- Name: users_priviliges; Type: VIEW; Schema: public; Owner: docker
--

CREATE VIEW public.users_priviliges AS
 SELECT usersroles.id_user,
    role.role_name
   FROM ((public.role
     JOIN public.usersroles USING (id_role))
     JOIN public."user" USING (id_user));


ALTER VIEW public.users_priviliges OWNER TO docker;

--
-- TOC entry 243 (class 1259 OID 25314)
-- Name: users_status; Type: VIEW; Schema: public; Owner: docker
--

CREATE VIEW public.users_status AS
SELECT
    NULL::integer AS id_user,
    NULL::character varying AS email,
    NULL::character varying AS status;


ALTER VIEW public.users_status OWNER TO docker;

--
-- TOC entry 232 (class 1259 OID 16989)
-- Name: watchlist; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.watchlist (
    id_user integer NOT NULL,
    id_asset integer NOT NULL
);


ALTER TABLE public.watchlist OWNER TO docker;

--
-- TOC entry 3510 (class 0 OID 16925)
-- Dependencies: 226
-- Data for Name: asset; Type: TABLE DATA; Schema: public; Owner: docker
--

INSERT INTO public.asset OVERRIDING SYSTEM VALUE VALUES (1, 2, 1, 'Apple', 'AAPL', 1);
INSERT INTO public.asset OVERRIDING SYSTEM VALUE VALUES (2, 2, 1, 'Microsoft', 'MSFT', 1);
INSERT INTO public.asset OVERRIDING SYSTEM VALUE VALUES (3, 1, 2, 'Bitcoin', 'BTC', 1);
INSERT INTO public.asset OVERRIDING SYSTEM VALUE VALUES (4, 2, 3, 'iShares 20 Plus Year Treasury Bond ETF', 'TLT', 1);


--
-- TOC entry 3508 (class 0 OID 16914)
-- Dependencies: 224
-- Data for Name: asset_type; Type: TABLE DATA; Schema: public; Owner: docker
--

INSERT INTO public.asset_type OVERRIDING SYSTEM VALUE VALUES (2, 'Cryptocurrency');
INSERT INTO public.asset_type OVERRIDING SYSTEM VALUE VALUES (3, 'ETF');
INSERT INTO public.asset_type OVERRIDING SYSTEM VALUE VALUES (1, 'Stock');
INSERT INTO public.asset_type OVERRIDING SYSTEM VALUE VALUES (5, 'Index');


--
-- TOC entry 3504 (class 0 OID 16893)
-- Dependencies: 220
-- Data for Name: country; Type: TABLE DATA; Schema: public; Owner: docker
--

INSERT INTO public.country OVERRIDING SYSTEM VALUE VALUES (1, 'None');
INSERT INTO public.country OVERRIDING SYSTEM VALUE VALUES (2, 'United States');


--
-- TOC entry 3519 (class 0 OID 17027)
-- Dependencies: 235
-- Data for Name: currency; Type: TABLE DATA; Schema: public; Owner: docker
--

INSERT INTO public.currency OVERRIDING SYSTEM VALUE VALUES (1, 'United States Dollar', 'USD', '$');
INSERT INTO public.currency OVERRIDING SYSTEM VALUE VALUES (2, 'Polish Złoty', 'PLN', 'zł');


--
-- TOC entry 3528 (class 0 OID 33557)
-- Dependencies: 247
-- Data for Name: current_price; Type: TABLE DATA; Schema: public; Owner: docker
--

INSERT INTO public.current_price VALUES (1, 192.26, '2024-03-01 23:23:45+00');
INSERT INTO public.current_price VALUES (2, 380.19, '2024-03-01 23:37:15+00');
INSERT INTO public.current_price VALUES (3, 45312.36, '2024-12-01 23:23:45+00');
INSERT INTO public.current_price VALUES (4, 93.26, '2024-03-01 23:23:45+00');


--
-- TOC entry 3525 (class 0 OID 25331)
-- Dependencies: 244
-- Data for Name: forex; Type: TABLE DATA; Schema: public; Owner: docker
--

INSERT INTO public.forex VALUES (1, 2, 4.04, '2024-03-01 23:23:45+00');
INSERT INTO public.forex VALUES (2, 1, 0.98, '2024-03-01 23:23:45+00');


--
-- TOC entry 3512 (class 0 OID 16943)
-- Dependencies: 228
-- Data for Name: investment; Type: TABLE DATA; Schema: public; Owner: docker
--



--
-- TOC entry 3506 (class 0 OID 16901)
-- Dependencies: 222
-- Data for Name: market; Type: TABLE DATA; Schema: public; Owner: docker
--

INSERT INTO public.market OVERRIDING SYSTEM VALUE VALUES (2, 2, 'Nasdaq Stock Exchange', 'NASDAQ');
INSERT INTO public.market OVERRIDING SYSTEM VALUE VALUES (1, 1, '', '');


--
-- TOC entry 3502 (class 0 OID 16880)
-- Dependencies: 218
-- Data for Name: portfolio; Type: TABLE DATA; Schema: public; Owner: docker
--



--
-- TOC entry 3521 (class 0 OID 25266)
-- Dependencies: 237
-- Data for Name: role; Type: TABLE DATA; Schema: public; Owner: docker
--

INSERT INTO public.role OVERRIDING SYSTEM VALUE VALUES (1, 'User');
INSERT INTO public.role OVERRIDING SYSTEM VALUE VALUES (2, 'Moderator');
INSERT INTO public.role OVERRIDING SYSTEM VALUE VALUES (3, 'Administrator');


--
-- TOC entry 3527 (class 0 OID 25366)
-- Dependencies: 246
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: docker
--

INSERT INTO public.sessions OVERRIDING SYSTEM VALUE VALUES (8, 1, '9717b955b20365a52874dab5ff07ecbef18f2bdb087714835f8e3ba54831d050', NULL);


--
-- TOC entry 3524 (class 0 OID 25298)
-- Dependencies: 242
-- Data for Name: status; Type: TABLE DATA; Schema: public; Owner: docker
--

INSERT INTO public.status OVERRIDING SYSTEM VALUE VALUES (1, 'Verified');
INSERT INTO public.status OVERRIDING SYSTEM VALUE VALUES (2, 'Blocked');
INSERT INTO public.status OVERRIDING SYSTEM VALUE VALUES (3, 'Banned');


--
-- TOC entry 3515 (class 0 OID 16966)
-- Dependencies: 231
-- Data for Name: transaction; Type: TABLE DATA; Schema: public; Owner: docker
--



--
-- TOC entry 3513 (class 0 OID 16958)
-- Dependencies: 229
-- Data for Name: transaction_type; Type: TABLE DATA; Schema: public; Owner: docker
--

INSERT INTO public.transaction_type OVERRIDING SYSTEM VALUE VALUES (1, 'BUY');
INSERT INTO public.transaction_type OVERRIDING SYSTEM VALUE VALUES (2, 'SELL');


--
-- TOC entry 3500 (class 0 OID 16872)
-- Dependencies: 216
-- Data for Name: user; Type: TABLE DATA; Schema: public; Owner: docker
--

INSERT INTO public."user" OVERRIDING SYSTEM VALUE VALUES (1, 'test@gmail.com', '$2y$10$1Ad56Wyvuw6B18BmO0QsIOVnQPPgUM7b6AcJh73cVX8OVVMjshqJy', '3e97cbc1d3a257d4d748fd098392be537ba89196483668a9d48bdcee0a4e4a50', 1, 2);
INSERT INTO public."user" OVERRIDING SYSTEM VALUE VALUES (22, 'testerzy@testy.pl', '$2y$10$lLjiSfPqZL.s8QMcJrdBxeGBQ8OiJDI2O9qMoyxCroPzAfFYySWLa', '1814a8487ef35bda36b7b5acd1cb911bdad328c12e09cbf22d4eed067357cfa0', 1, 1);


--
-- TOC entry 3522 (class 0 OID 25273)
-- Dependencies: 238
-- Data for Name: usersroles; Type: TABLE DATA; Schema: public; Owner: docker
--

INSERT INTO public.usersroles VALUES (1, 1);
INSERT INTO public.usersroles VALUES (1, 2);
INSERT INTO public.usersroles VALUES (1, 3);
INSERT INTO public.usersroles VALUES (22, 1);


--
-- TOC entry 3516 (class 0 OID 16989)
-- Dependencies: 232
-- Data for Name: watchlist; Type: TABLE DATA; Schema: public; Owner: docker
--

INSERT INTO public.watchlist OVERRIDING SYSTEM VALUE VALUES (1, 2);
INSERT INTO public.watchlist OVERRIDING SYSTEM VALUE VALUES (1, 1);
INSERT INTO public.watchlist OVERRIDING SYSTEM VALUE VALUES (1, 3);
INSERT INTO public.watchlist OVERRIDING SYSTEM VALUE VALUES (1, 4);


--
-- TOC entry 3535 (class 0 OID 0)
-- Dependencies: 225
-- Name: asset_id_asset_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.asset_id_asset_seq', 4, true);


--
-- TOC entry 3536 (class 0 OID 0)
-- Dependencies: 223
-- Name: asset_type_id_asset_type_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.asset_type_id_asset_type_seq', 5, true);


--
-- TOC entry 3537 (class 0 OID 0)
-- Dependencies: 219
-- Name: country_id_country_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.country_id_country_seq', 2, true);


--
-- TOC entry 3538 (class 0 OID 0)
-- Dependencies: 234
-- Name: currency_id_currency_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.currency_id_currency_seq', 2, true);


--
-- TOC entry 3539 (class 0 OID 0)
-- Dependencies: 227
-- Name: investment_id_investment_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.investment_id_investment_seq', 23, true);


--
-- TOC entry 3540 (class 0 OID 0)
-- Dependencies: 221
-- Name: market_id_market_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.market_id_market_seq', 2, true);


--
-- TOC entry 3541 (class 0 OID 0)
-- Dependencies: 217
-- Name: portfolio_id_portfolio_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.portfolio_id_portfolio_seq', 84, true);


--
-- TOC entry 3542 (class 0 OID 0)
-- Dependencies: 236
-- Name: role_id_role_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.role_id_role_seq', 3, true);


--
-- TOC entry 3543 (class 0 OID 0)
-- Dependencies: 245
-- Name: sessions_id_session_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.sessions_id_session_seq', 8, true);


--
-- TOC entry 3544 (class 0 OID 0)
-- Dependencies: 241
-- Name: status_id_status_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.status_id_status_seq', 3, true);


--
-- TOC entry 3545 (class 0 OID 0)
-- Dependencies: 230
-- Name: transaction_id_transaction_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.transaction_id_transaction_seq', 82, true);


--
-- TOC entry 3546 (class 0 OID 0)
-- Dependencies: 233
-- Name: transaction_type_id_transaction_type_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.transaction_type_id_transaction_type_seq', 1, false);


--
-- TOC entry 3547 (class 0 OID 0)
-- Dependencies: 215
-- Name: user_id_user_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.user_id_user_seq', 23, true);


--
-- TOC entry 3310 (class 2606 OID 16931)
-- Name: asset asset_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.asset
    ADD CONSTRAINT asset_pkey PRIMARY KEY (id_asset);


--
-- TOC entry 3308 (class 2606 OID 16920)
-- Name: asset_type asset_type_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.asset_type
    ADD CONSTRAINT asset_type_pkey PRIMARY KEY (id_asset_type);


--
-- TOC entry 3304 (class 2606 OID 16897)
-- Name: country country_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.country
    ADD CONSTRAINT country_pkey PRIMARY KEY (id_country);


--
-- TOC entry 3320 (class 2606 OID 17033)
-- Name: currency currency_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.currency
    ADD CONSTRAINT currency_pkey PRIMARY KEY (id_currency);


--
-- TOC entry 3328 (class 2606 OID 25339)
-- Name: forex forex_id_from_id_to_key; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.forex
    ADD CONSTRAINT forex_id_from_id_to_key UNIQUE (id_from, id_to);


--
-- TOC entry 3330 (class 2606 OID 25337)
-- Name: forex forex_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.forex
    ADD CONSTRAINT forex_pkey PRIMARY KEY (id_from);


--
-- TOC entry 3312 (class 2606 OID 16947)
-- Name: investment investment_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.investment
    ADD CONSTRAINT investment_pkey PRIMARY KEY (id_investment);


--
-- TOC entry 3306 (class 2606 OID 16907)
-- Name: market market_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.market
    ADD CONSTRAINT market_pkey PRIMARY KEY (id_market);


--
-- TOC entry 3300 (class 2606 OID 17072)
-- Name: portfolio portfolio_id_user_name_key; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.portfolio
    ADD CONSTRAINT portfolio_id_user_name_key UNIQUE (id_user, name);


--
-- TOC entry 3302 (class 2606 OID 16886)
-- Name: portfolio portfolio_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.portfolio
    ADD CONSTRAINT portfolio_pkey PRIMARY KEY (id_portfolio);


--
-- TOC entry 3322 (class 2606 OID 25272)
-- Name: role role_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.role
    ADD CONSTRAINT role_pkey PRIMARY KEY (id_role);


--
-- TOC entry 3332 (class 2606 OID 25372)
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id_session);


--
-- TOC entry 3326 (class 2606 OID 25302)
-- Name: status status_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.status
    ADD CONSTRAINT status_pkey PRIMARY KEY (id_status);


--
-- TOC entry 3316 (class 2606 OID 16970)
-- Name: transaction transaction_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.transaction
    ADD CONSTRAINT transaction_pkey PRIMARY KEY (id_transaction);


--
-- TOC entry 3314 (class 2606 OID 16964)
-- Name: transaction_type transaction_type_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.transaction_type
    ADD CONSTRAINT transaction_type_pkey PRIMARY KEY (id_transaction_type);


--
-- TOC entry 3334 (class 2606 OID 33563)
-- Name: current_price unq_asset; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.current_price
    ADD CONSTRAINT unq_asset PRIMARY KEY (id_asset);


--
-- TOC entry 3298 (class 2606 OID 16878)
-- Name: user user_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public."user"
    ADD CONSTRAINT user_pkey PRIMARY KEY (id_user);


--
-- TOC entry 3324 (class 2606 OID 25287)
-- Name: usersroles usersroles_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.usersroles
    ADD CONSTRAINT usersroles_pkey PRIMARY KEY (id_user, id_role);


--
-- TOC entry 3318 (class 2606 OID 17005)
-- Name: watchlist watchlist_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.watchlist
    ADD CONSTRAINT watchlist_pkey PRIMARY KEY (id_user, id_asset);


--
-- TOC entry 3498 (class 2618 OID 25317)
-- Name: users_status _RETURN; Type: RULE; Schema: public; Owner: docker
--

CREATE OR REPLACE VIEW public.users_status AS
 SELECT "user".id_user,
    "user".email,
    status.status_name AS status
   FROM (public.status
     JOIN public."user" USING (id_status))
  GROUP BY "user".id_user, status.status_name;


--
-- TOC entry 3338 (class 2606 OID 17034)
-- Name: asset asset_id_currency_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.asset
    ADD CONSTRAINT asset_id_currency_fkey FOREIGN KEY (id_currency) REFERENCES public.currency(id_currency) NOT VALID;


--
-- TOC entry 3339 (class 2606 OID 16932)
-- Name: asset asset_id_market_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.asset
    ADD CONSTRAINT asset_id_market_fkey FOREIGN KEY (id_market) REFERENCES public.market(id_market) NOT VALID;


--
-- TOC entry 3340 (class 2606 OID 16937)
-- Name: asset asset_id_type_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.asset
    ADD CONSTRAINT asset_id_type_fkey FOREIGN KEY (id_asset_type) REFERENCES public.asset_type(id_asset_type) NOT VALID;


--
-- TOC entry 3352 (class 2606 OID 33564)
-- Name: current_price current_price2_id_asset_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.current_price
    ADD CONSTRAINT current_price2_id_asset_fkey FOREIGN KEY (id_asset) REFERENCES public.asset(id_asset);


--
-- TOC entry 3349 (class 2606 OID 25340)
-- Name: forex forex_id_from_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.forex
    ADD CONSTRAINT forex_id_from_fkey FOREIGN KEY (id_from) REFERENCES public.currency(id_currency) NOT VALID;


--
-- TOC entry 3350 (class 2606 OID 25345)
-- Name: forex forex_id_to_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.forex
    ADD CONSTRAINT forex_id_to_fkey FOREIGN KEY (id_to) REFERENCES public.currency(id_currency) NOT VALID;


--
-- TOC entry 3341 (class 2606 OID 16953)
-- Name: investment investment_id_asset_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.investment
    ADD CONSTRAINT investment_id_asset_fkey FOREIGN KEY (id_asset) REFERENCES public.asset(id_asset) NOT VALID;


--
-- TOC entry 3342 (class 2606 OID 25321)
-- Name: investment investment_id_portfolio_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.investment
    ADD CONSTRAINT investment_id_portfolio_fkey FOREIGN KEY (id_portfolio) REFERENCES public.portfolio(id_portfolio) ON DELETE CASCADE NOT VALID;


--
-- TOC entry 3337 (class 2606 OID 16908)
-- Name: market market_id_country_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.market
    ADD CONSTRAINT market_id_country_fkey FOREIGN KEY (id_country) REFERENCES public.country(id_country) NOT VALID;


--
-- TOC entry 3336 (class 2606 OID 16887)
-- Name: portfolio portfolio_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.portfolio
    ADD CONSTRAINT portfolio_id_user_fkey FOREIGN KEY (id_user) REFERENCES public."user"(id_user);


--
-- TOC entry 3351 (class 2606 OID 25373)
-- Name: sessions sessions_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_id_user_fkey FOREIGN KEY (id_user) REFERENCES public."user"(id_user) NOT VALID;


--
-- TOC entry 3343 (class 2606 OID 25326)
-- Name: transaction transaction_id_investment_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.transaction
    ADD CONSTRAINT transaction_id_investment_fkey FOREIGN KEY (id_investment) REFERENCES public.investment(id_investment) ON DELETE CASCADE NOT VALID;


--
-- TOC entry 3344 (class 2606 OID 16981)
-- Name: transaction transaction_id_transaction_type_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.transaction
    ADD CONSTRAINT transaction_id_transaction_type_fkey FOREIGN KEY (id_transaction_type) REFERENCES public.transaction_type(id_transaction_type) NOT VALID;


--
-- TOC entry 3335 (class 2606 OID 25305)
-- Name: user user_id_status_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public."user"
    ADD CONSTRAINT user_id_status_fkey FOREIGN KEY (id_status) REFERENCES public.status(id_status) NOT VALID;


--
-- TOC entry 3347 (class 2606 OID 25281)
-- Name: usersroles usersroles_id_role_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.usersroles
    ADD CONSTRAINT usersroles_id_role_fkey FOREIGN KEY (id_role) REFERENCES public.role(id_role);


--
-- TOC entry 3348 (class 2606 OID 25354)
-- Name: usersroles usersroles_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.usersroles
    ADD CONSTRAINT usersroles_id_user_fkey FOREIGN KEY (id_user) REFERENCES public."user"(id_user) ON DELETE CASCADE NOT VALID;


--
-- TOC entry 3345 (class 2606 OID 16999)
-- Name: watchlist watchlist_id_asset_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.watchlist
    ADD CONSTRAINT watchlist_id_asset_fkey FOREIGN KEY (id_asset) REFERENCES public.asset(id_asset) NOT VALID;


--
-- TOC entry 3346 (class 2606 OID 25359)
-- Name: watchlist watchlist_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.watchlist
    ADD CONSTRAINT watchlist_id_user_fkey FOREIGN KEY (id_user) REFERENCES public."user"(id_user) ON DELETE CASCADE NOT VALID;


-- Completed on 2024-01-25 02:38:31 UTC

--
-- PostgreSQL database dump complete
--

