--
-- PostgreSQL database dump
--

SET client_encoding = 'SQL_ASCII';
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: tng_submission_permission; Type: TABLE; Schema: public; Owner: tng_admin; Tablespace: 
--

CREATE TABLE tng_submission_permission (
    perm_id serial NOT NULL,
    sub_id bigint NOT NULL,
    uid bigint NOT NULL
);


ALTER TABLE public.tng_submission_permission OWNER TO tng_admin;

--
-- Name: pk_perm_id; Type: CONSTRAINT; Schema: public; Owner: tng_admin; Tablespace: 
--

ALTER TABLE ONLY tng_submission_permission
    ADD CONSTRAINT pk_perm_id PRIMARY KEY (perm_id);


ALTER INDEX public.pk_perm_id OWNER TO tng_admin;

--
-- Name: fk_sub_id; Type: FK CONSTRAINT; Schema: public; Owner: tng_admin
--

ALTER TABLE ONLY tng_submission_permission
    ADD CONSTRAINT fk_sub_id FOREIGN KEY (sub_id) REFERENCES tng_form_submission(form_submission_id);


--
-- Name: fk_uid; Type: FK CONSTRAINT; Schema: public; Owner: tng_admin
--

ALTER TABLE ONLY tng_submission_permission
    ADD CONSTRAINT fk_uid FOREIGN KEY (uid) REFERENCES tng_user(uid);


--
-- Name: tng_submission_permission; Type: ACL; Schema: public; Owner: tng_admin
--

REVOKE ALL ON TABLE tng_submission_permission FROM PUBLIC;
REVOKE ALL ON TABLE tng_submission_permission FROM tng_admin;
GRANT ALL ON TABLE tng_submission_permission TO tng_admin;
GRANT INSERT,SELECT,DELETE ON TABLE tng_submission_permission TO tng_readwrite;
GRANT SELECT ON TABLE tng_submission_permission TO tng_readonly;


--
-- Name: tng_submission_permission_perm_id_seq; Type: ACL; Schema: public; Owner: tng_admin
--

REVOKE ALL ON TABLE tng_submission_permission_perm_id_seq FROM PUBLIC;
REVOKE ALL ON TABLE tng_submission_permission_perm_id_seq FROM tng_admin;
GRANT ALL ON TABLE tng_submission_permission_perm_id_seq TO tng_admin;
GRANT SELECT,UPDATE ON TABLE tng_submission_permission_perm_id_seq TO tng_readwrite;
GRANT SELECT ON TABLE tng_submission_permission_perm_id_seq TO tng_readonly;


--
-- PostgreSQL database dump complete
--

